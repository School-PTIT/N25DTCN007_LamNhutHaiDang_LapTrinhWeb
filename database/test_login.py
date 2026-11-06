import urllib.request, urllib.parse, re, http.cookiejar

def test_login(email, password, expected_role):
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    
    resp = opener.open('http://127.0.0.1:8080/login.php')
    html = resp.read().decode('utf-8')
    match = re.search(r'name="csrf_token"\s+value="([a-f0-9]+)"', html)
    if not match:
        print('FAILED to find CSRF token')
        return False
    csrf = match.group(1)
    
    data = urllib.parse.urlencode({'email': email, 'password': password, 'csrf_token': csrf}).encode('utf-8')
    req = urllib.request.Request('http://127.0.0.1:8080/login.php', data=data, method='POST')
    resp2 = opener.open(req)
    
    if expected_role == 'admin':
        resp3 = opener.open('http://127.0.0.1:8080/admin/index.php')
        dash_html = resp3.read().decode('utf-8')
        if 'SYSTEM TELEMETRY DASHBOARD' in dash_html:
            print(f'ADMIN LOGIN ({email}): SUCCESS! Admin dashboard accessible.')
            return True
        else:
            print(f'ADMIN LOGIN ({email}): FAILED to access dashboard.')
            return False
    else:
        resp3 = opener.open('http://127.0.0.1:8080/profile.php')
        prof_html = resp3.read().decode('utf-8')
        if 'Nguyễn Hoàng Long' in prof_html or 'profile.php' in resp3.url:
            print(f'CUSTOMER LOGIN ({email}): SUCCESS! Profile verified.')
            return True
        else:
            print(f'CUSTOMER LOGIN ({email}): FAILED.')
            return False

if __name__ == '__main__':
    test_login('admin@lab.ptit.edu.vn', 'admin123', 'admin')
    test_login('khachhang1@gmail.com', 'customer123', 'customer')
