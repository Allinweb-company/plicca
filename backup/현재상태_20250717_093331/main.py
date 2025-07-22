"""
Flask wrapper for PHP-based Fintree payment test site
This provides a Flask WSGI application that serves PHP files
"""

from flask import Flask, request, Response
import subprocess
import os
import sys
from urllib.parse import urlparse
from datetime import datetime

app = Flask(__name__)

def execute_php(file_path, method='GET', post_data='', query_string=''):
    """Execute PHP file and return the output"""
    try:
        # Set environment variables for PHP
        env = os.environ.copy()
        env['REQUEST_METHOD'] = method
        env['REQUEST_URI'] = request.path
        env['QUERY_STRING'] = query_string
        env['SERVER_NAME'] = request.host.split(':')[0] if request.host else 'localhost'
        env['SERVER_PORT'] = '5000'
        env['REMOTE_ADDR'] = request.remote_addr if request.remote_addr else '127.0.0.1'
        env['HTTP_HOST'] = request.headers.get('Host', 'localhost:5000')
        env['HTTP_USER_AGENT'] = request.headers.get('User-Agent', 'Flask-PHP/1.0')
        env['CONTENT_TYPE'] = request.headers.get('Content-Type', 'application/x-www-form-urlencoded')
        env['CONTENT_LENGTH'] = str(len(post_data))
        
        # For JSON API requests, pass data via environment variable
        if post_data and request.headers.get('Content-Type', '').startswith('application/json'):
            env['JSON_INPUT'] = post_data
        
        # Additional CGI environment variables
        env['SCRIPT_NAME'] = f'/{file_path}'
        env['PATH_INFO'] = ''
        env['SERVER_SOFTWARE'] = 'Flask-PHP/1.0'
        env['GATEWAY_INTERFACE'] = 'CGI/1.1'
        env['SERVER_PROTOCOL'] = 'HTTP/1.1'
        env['HTTPS'] = 'on' if request.is_secure else 'off'
        
        # For POST requests, prepare the input data
        php_input = post_data if post_data else ''
        
        # Create a simple PHP wrapper that sets $_GET and handles php://input properly
        # Escape php_input for safe inclusion in PHP code
        php_input_escaped = php_input.replace("'", "\\'").replace("\\", "\\\\")
        query_string_escaped = query_string.replace("'", "\\'").replace("\\", "\\\\")
        
        php_wrapper = f"""<?php
// Set superglobals manually
parse_str('{query_string_escaped}', $_GET);

// Handle different content types
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
if ('{method}' === 'POST') {{
    if (strpos($content_type, 'application/json') !== false) {{
        // For JSON API endpoints, don't parse into $_POST, let php://input work
        // The API files will read from php://input directly
    }} else {{
        // Parse POST data
        $post_data = '{php_input_escaped}';
        if (!empty($post_data)) {{
            parse_str($post_data, $_POST);
        }}
    }}
}}

// Include the actual PHP file
include '{file_path}';
?>"""
        
        # Execute PHP with the wrapper and POST data as stdin
        process = subprocess.Popen(
            ['php'],
            stdin=subprocess.PIPE,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            env=env,
            cwd=os.getcwd()
        )
        
        # Send the wrapper code first, then the POST data
        input_data = php_wrapper.encode('utf-8')
        if method == 'POST' and post_data and not request.headers.get('Content-Type', '').startswith('application/json'):
            # Only append POST data for form-encoded requests, not JSON
            input_data += b'\n' + post_data.encode('utf-8')
        
        stdout, stderr = process.communicate(input=input_data)
        
        if process.returncode != 0:
            return None, f"PHP Error: {stderr.decode()}", {}
        
        # Parse PHP output - for regular PHP files, just return the content
        output = stdout.decode('utf-8', errors='ignore')
        
        # Check if there are HTTP headers (CGI mode)
        headers = {}
        body = output
        
        if output.startswith('Content-Type:') or '\nContent-Type:' in output[:200]:
            # CGI mode with headers
            if '\r\n\r\n' in output:
                headers_part, body = output.split('\r\n\r\n', 1)
            elif '\n\n' in output:
                headers_part, body = output.split('\n\n', 1)
            else:
                # No double newline found, treat entire output as body
                headers_part = ''
                body = output
            
            # Parse headers if they exist
            if headers_part:
                for line in headers_part.split('\n'):
                    if ':' in line and line.strip():
                        try:
                            header_name, header_value = line.split(':', 1)
                            header_name = header_name.strip()
                            header_value = header_value.strip()
                            if header_name and header_value:
                                headers[header_name] = header_value
                        except ValueError:
                            continue
        
        return body, None, headers
        
    except Exception as e:
        return None, f"Server Error: {str(e)}", {}

@app.route('/', methods=['GET', 'POST'])
def index():
    # Log all requests for debugging
    with open('debug.log', 'a') as f:
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Request: {request.method} /\n")
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] POST data: {request.get_data(as_text=True)}\n")
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Form data: {dict(request.form)}\n")
    
    return serve_php('index.php')

@app.route('/<path:filename>', methods=['GET', 'POST'])
def serve_file(filename):
    # Log all requests for debugging (모든 요청에 대해 먼저 로그 기록)
    with open('debug.log', 'a') as f:
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Request: {request.method} {filename}\n")
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] POST data: {request.get_data(as_text=True)}\n")
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Form data: {dict(request.form)}\n")
    
    # Handle HTML files directly
    if filename.endswith('.html'):
        try:
            with open(filename, 'r', encoding='utf-8') as f:
                content = f.read()
            return Response(content, mimetype='text/html')
        except FileNotFoundError:
            return Response("File not found", status=404)
        except Exception as e:
            return Response(f"Error serving file: {str(e)}", status=500)
    
    # Security check - prevent directory traversal
    if '..' in filename or filename.startswith('/'):
        return "Access denied", 403
    
    # Check if PHP file exists
    if filename.endswith('.php') and os.path.exists(filename):
        return serve_php(filename)
    
    # For non-PHP files, try to serve them as static files
    if os.path.exists(filename):
        with open(filename, 'rb') as f:
            content = f.read()
        
        # Determine content type
        if filename.endswith('.css'):
            content_type = 'text/css'
        elif filename.endswith('.js'):
            content_type = 'application/javascript'
        elif filename.endswith('.png'):
            content_type = 'image/png'
        elif filename.endswith('.jpg') or filename.endswith('.jpeg'):
            content_type = 'image/jpeg'
        elif filename.endswith('.gif'):
            content_type = 'image/gif'
        elif filename.endswith('.svg'):
            content_type = 'image/svg+xml'
        elif filename.endswith('.html'):
            content_type = 'text/html'
        else:
            content_type = 'application/octet-stream'
        
        return Response(content, content_type=content_type)
    
    return "File not found", 404

def serve_php(filename):
    """Serve a PHP file"""
    if not os.path.exists(filename):
        return "File not found", 404
    
    # Get POST data if available
    post_data = ''
    if request.method == 'POST':
        if request.content_type and 'application/x-www-form-urlencoded' in request.content_type:
            # Convert form data to URL-encoded string
            post_data = '&'.join([f"{k}={v}" for k, v in request.form.items()])
        else:
            post_data = request.get_data(as_text=True)
    
    # Get query string
    query_string = request.query_string.decode('utf-8')
    
    # Execute PHP
    body, error, headers = execute_php(filename, request.method, post_data, query_string)
    
    if error:
        return f"<html><body><h1>PHP Error</h1><pre>{error}</pre></body></html>", 500
    
    # Handle redirects from PHP
    if headers and 'Location' in headers:
        from flask import redirect
        return redirect(headers['Location'])
    
    # Create response
    response = Response(body)
    
    # Set headers from PHP
    if headers:
        for name, value in headers.items():
            if name.lower() != 'location':  # Skip location header as it's handled above
                response.headers[name] = value
    
    # Set default content type if not specified
    if 'Content-Type' not in response.headers:
        response.headers['Content-Type'] = 'text/html; charset=utf-8'
    
    return response

if __name__ == "__main__":
    # Check if PHP is available
    try:
        subprocess.run(['php', '--version'], capture_output=True, check=True)
        print("✓ PHP is available")
    except (subprocess.CalledProcessError, FileNotFoundError):
        print("❌ Error: PHP is not installed or not in PATH")
        sys.exit(1)
    
    print("🚀 Starting Fintree Payment Test Server")
    print("📁 Serving PHP files from current directory")
    app.run(host='0.0.0.0', port=5000, debug=True)