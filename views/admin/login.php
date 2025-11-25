<div class="container" style="max-width: 400px; margin-top: 100px;">
    <h2 style="margin-bottom: 20px;">Admin Access</h2>
    <form id="loginForm">
        <div style="margin-bottom: 15px;">
            <label>Email</label>
            <input type="email" id="email" class="form-control" style="width: 100%; padding: 10px;" required>
        </div>
        <div style="margin-bottom: 15px;">
            <label>Password</label>
            <input type="password" id="password" class="form-control" style="width: 100%; padding: 10px;" required>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
        <p id="msg" style="color: red; margin-top: 10px;"></p>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const res = await fetch('/api/admin/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
    });
    const data = await res.json();
    
    if (res.ok) {
        window.location.href = data.redirect;
    } else {
        document.getElementById('msg').innerText = data.error;
    }
});
</script>