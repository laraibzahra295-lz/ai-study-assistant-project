// ============================================
// AI Study Assistant - app.js
// ============================================

const API_BASE = './api';
const AUTH_KEY = 'aisa_user';

/* ===============================
   ESCAPE HTML
=============================== */
function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

/* ===============================
   AUTH HELPERS
=============================== */
function getUser() {
    try { return JSON.parse(localStorage.getItem(AUTH_KEY) || 'null'); }
    catch { return null; }
}
function setUser(u) {
    if (u) localStorage.setItem(AUTH_KEY, JSON.stringify(u));
    else   localStorage.removeItem(AUTH_KEY);
}
function requireAuth() {
    if (!getUser()) { location.href = 'index.html'; return false; }
    return true;
}

/* ===============================
   TOAST
=============================== */
function toast(title, desc, variant) {
    let host = document.querySelector('.toast-container');
    if (!host) {
        host = document.createElement('div');
        host.className = 'toast-container';
        document.body.appendChild(host);
    }
    const el = document.createElement('div');
    el.className = 'toast' + (variant === 'destructive' ? ' error' : '');
    el.innerHTML = `<div class="toast-title">${escapeHtml(title)}</div>` +
        (desc ? `<div class="toast-desc">${escapeHtml(desc)}</div>` : '');
    host.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

/* ===============================
   AUTH MODAL
=============================== */
function openAuthModal(tab) {
    let overlay = document.getElementById('authOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'authOverlay';
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
        <div class="modal" id="authModal">
            <div class="modal-tabs">
                <button id="tabLogin" class="active" onclick="switchTab('login')">Login</button>
                <button id="tabSignup" onclick="switchTab('signup')">Sign Up</button>
            </div>
            <div id="loginForm">
                <div class="form-group"><label>Email</label><input class="input" id="loginEmail" type="email" placeholder="you@example.com"/></div>
                <div class="form-group"><label>Password</label><input class="input" id="loginPass" type="password" placeholder="Password"/></div>
                <div id="loginErr" style="color:#ef4444;font-size:.85rem;margin-bottom:10px;display:none"></div>
                <button class="btn btn-gradient full" id="loginSubmit" onclick="doLogin()">Login</button>
                <p style="text-align:center;margin-top:14px;font-size:.85rem;color:#6b7280">
                    No account? <a href="#" onclick="switchTab('signup')" style="color:#667eea;font-weight:600">Sign Up</a>
                </p>
            </div>
            <div id="signupForm" style="display:none">
                <div class="form-group"><label>Name</label><input class="input" id="signupName" placeholder="Full Name"/></div>
                <div class="form-group"><label>Email</label><input class="input" id="signupEmail" type="email" placeholder="you@example.com"/></div>
                <div class="form-group"><label>Password</label><input class="input" id="signupPass" type="password" placeholder="Min. 6 characters"/></div>
                <div id="signupErr" style="color:#ef4444;font-size:.85rem;margin-bottom:10px;display:none"></div>
                <button class="btn btn-gradient full" id="signupSubmit" onclick="doSignup()">Create Account</button>
                <p style="text-align:center;margin-top:14px;font-size:.85rem;color:#6b7280">
                    Have account? <a href="#" onclick="switchTab('login')" style="color:#667eea;font-weight:600">Login</a>
                </p>
            </div>
            <button onclick="closeAuthModal()" style="position:absolute;top:12px;right:16px;font-size:1.5rem;background:none;border:none;cursor:pointer;color:#9ca3af">&times;</button>
        </div>`;
        overlay.style.position = 'relative';
        document.body.appendChild(overlay);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeAuthModal(); });
    }
    overlay.classList.add('open');
    switchTab(tab || 'login');
}

function closeAuthModal() {
    document.getElementById('authOverlay')?.classList.remove('open');
}

function switchTab(t) {
    const isLogin = t === 'login';
    document.getElementById('loginForm').style.display  = isLogin ? '' : 'none';
    document.getElementById('signupForm').style.display = isLogin ? 'none' : '';
    document.getElementById('tabLogin').classList.toggle('active', isLogin);
    document.getElementById('tabSignup').classList.toggle('active', !isLogin);
}

async function doLogin() {
    const email = document.getElementById('loginEmail').value.trim();
    const pass  = document.getElementById('loginPass').value;
    const errEl = document.getElementById('loginErr');
    const btn   = document.getElementById('loginSubmit');
    errEl.style.display = 'none';

    if (!email || !pass) { errEl.textContent = 'Email and password required'; errEl.style.display = ''; return; }

    btn.disabled = true; btn.textContent = 'Logging in...';
    try {
        const res  = await fetch(`${API_BASE}/auth.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', email, password: pass }),
        });
        const data = await res.json();
        if (!res.ok) { errEl.textContent = data.error || 'Login failed'; errEl.style.display = ''; return; }
        setUser(data.user);
        closeAuthModal();
        location.reload();
    } catch (e) {
        errEl.textContent = 'Network error. Try again.'; errEl.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = 'Login';
    }
}

async function doSignup() {
    const name  = document.getElementById('signupName').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const pass  = document.getElementById('signupPass').value;
    const errEl = document.getElementById('signupErr');
    const btn   = document.getElementById('signupSubmit');
    errEl.style.display = 'none';

    if (!name || !email || !pass) { errEl.textContent = 'All fields are required'; errEl.style.display = ''; return; }
    if (pass.length < 6) { errEl.textContent = 'Password must be at least 6 characters'; errEl.style.display = ''; return; }

    btn.disabled = true; btn.textContent = 'Creating account...';
    try {
        const res  = await fetch(`${API_BASE}/auth.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'signup', name, email, password: pass }),
        });
        const data = await res.json();
        if (!res.ok) { errEl.textContent = data.error || 'Signup failed'; errEl.style.display = ''; return; }
        setUser(data.user);
        closeAuthModal();
        location.reload();
    } catch (e) {
        errEl.textContent = 'Network error. Try again.'; errEl.style.display = '';
    } finally {
        btn.disabled = false; btn.textContent = 'Create Account';
    }
}

/* ===============================
   NAVBAR
=============================== */
function renderNavbar(activePage) {
    const user = getUser();
    const pages = [
        { href: 'index.html',       label: 'Home' },
        { href: 'about.html',       label: 'About' },
        { href: 'features.html',    label: 'Features' },
        { href: 'how-it-works.html',label: 'How It Works' },
        { href: 'study-tools.html', label: 'Study Tools' },
        { href: 'contact.html',     label: 'Contact' },
    ];
    const links = pages.map(p =>
        `<a href="${p.href}" class="${activePage === p.href ? 'active' : ''}">${p.label}</a>`
    ).join('');

    const userHtml = user
        ? `<div class="user-pill">
               <div class="avatar">${escapeHtml(user.name[0].toUpperCase())}</div>
               <span class="greet">Hi, ${escapeHtml(user.name.split(' ')[0])}!</span>
           </div>
           <button class="btn btn-outline-white" id="logoutBtn">Logout</button>`
        : `<button class="btn btn-outline-white" id="loginBtn">Login</button>
           <button class="btn btn-white" id="signupBtn">Sign Up</button>`;

    const navEl = document.getElementById('navbar');
    if (!navEl) return;
    navEl.innerHTML = `
    <header class="navbar">
        <div class="nav-inner">
            <a href="index.html" class="nav-brand">🎓 AI Study Assistant</a>
            <nav class="nav-links">${links}</nav>
            <div class="nav-actions">${userHtml}</div>
        </div>
    </header>`;

    document.getElementById('logoutBtn')?.addEventListener('click', () => { setUser(null); location.reload(); });
    document.getElementById('loginBtn')?.addEventListener('click', () => openAuthModal('login'));
    document.getElementById('signupBtn')?.addEventListener('click', () => openAuthModal('signup'));
}

/* ===============================
   FOOTER
=============================== */
function renderFooter() {
    const el = document.getElementById('footer');
    if (!el) return;
    el.innerHTML = `
    <footer class="footer">
        <div class="footer-grid">
            <div>
                <h4>🎓 AI Study Assistant</h4>
                <p>AI-powered learning platform for students worldwide.</p>
            </div>
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="study-tools.html">Study Tools</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li><span>support@ai-study-assistant.com</span></li>
                    <li><span>Monday–Friday, 9am–6pm</span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 AI Study Assistant. All rights reserved.</p>
        </div>
    </footer>`;
}
