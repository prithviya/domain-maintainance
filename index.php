<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>AMC Admin Login | Domain & Hosting Control</title>
  <!-- Google Fonts + Font Awesome 6 -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #0b2b3b 0%, #0a1c2a 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow-x: hidden;
    }

    /* main login container */
    .login-container {
      width: 100%;
      max-width: 480px;
      margin: 24px;
      z-index: 10;
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* card style */
    .login-card {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(0px);
      border-radius: 48px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(79, 157, 166, 0.2);
      overflow: hidden;
      transition: transform 0.2s;
    }

    /* header branding */
    .brand-header {
      background: linear-gradient(120deg, #0a2f3e 0%, #0c4b5a 100%);
      padding: 32px 32px 28px;
      text-align: center;
      color: white;
    }
    .logo-icon {
      font-size: 3rem;
      margin-bottom: 12px;
      display: inline-block;
      background: rgba(255,255,255,0.15);
      width: 80px;
      height: 80px;
      line-height: 80px;
      border-radius: 60px;
    }
    .brand-header h1 {
      font-size: 1.9rem;
      font-weight: 700;
      letter-spacing: -0.3px;
      margin-bottom: 8px;
    }
    .brand-header p {
      font-size: 0.85rem;
      opacity: 0.85;
      font-weight: 400;
    }

    /* form area */
    .form-area {
      padding: 36px 32px 40px;
    }
   
    /* input groups */
    .input-group {
      margin-bottom: 24px;
      position: relative;
    }
    .input-group label {
      display: block;
      font-size: 0.85rem;
      font-weight: 500;
      color: #1e3a4d;
      margin-bottom: 8px;
    }
    .input-wrapper {
      display: flex;
      align-items: center;
      background: #f8fafc;
      border: 1.5px solid #e2e8f0;
      border-radius: 28px;
      transition: all 0.2s;
      padding: 4px 8px 4px 20px;
    }
    .input-wrapper:focus-within {
      border-color: #2c6e7a;
      box-shadow: 0 0 0 3px rgba(44, 110, 122, 0.2);
      background: white;
    }
    .input-wrapper i {
      color: #7f8fa4;
      font-size: 1.1rem;
      margin-right: 12px;
    }
    .input-wrapper input {
      flex: 1;
      border: none;
      background: transparent;
      padding: 14px 0;
      font-size: 0.95rem;
      font-weight: 500;
      outline: none;
      font-family: 'Inter', sans-serif;
    }
    .input-wrapper input::placeholder {
      color: #b9c3d4;
      font-weight: 400;
    }

    /* password toggle */
    .toggle-password {
      cursor: pointer;
      margin-right: 12px;
      color: #8b9ab0;
      transition: color 0.2s;
    }
    .toggle-password:hover {
      color: #2c6e7a;
    }

    /* row options */
    .options-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 16px 0 28px;
      font-size: 0.85rem;
    }
    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      color: #334155;
    }
    .checkbox-label input {
      width: 16px;
      height: 16px;
      accent-color: #2c6e7a;
      cursor: pointer;
    }
    .forgot-link {
      color: #2c6e7a;
      text-decoration: none;
      font-weight: 500;
      transition: 0.2s;
    }
    .forgot-link:hover {
      text-decoration: underline;
      color: #1e555f;
    }

    /* login button */
    .login-btn {
      width: 100%;
      background: linear-gradient(95deg, #1f6e7c 0%, #0f5a68 100%);
      border: none;
      padding: 15px;
      border-radius: 40px;
      font-weight: 700;
      font-size: 1rem;
      color: white;
      cursor: pointer;
      transition: all 0.25s;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 10px rgba(31, 110, 124, 0.3);
    }
    .login-btn:hover {
      background: linear-gradient(95deg, #0f5a68, #0a4854);
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -8px rgba(31, 110, 124, 0.5);
    }
    .login-btn:active {
      transform: translateY(1px);
    }

    /* error message */
    .error-message {
      background: #fff5f5;
      border-left: 4px solid #e53e3e;
      padding: 12px 18px;
      border-radius: 20px;
      margin-bottom: 24px;
      color: #c53030;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.4s ease;
    }
    @keyframes shake {
      0%,100%{ transform: translateX(0); }
      25%{ transform: translateX(-5px); }
      75%{ transform: translateX(5px); }
    }
    .hidden {
      display: none;
    }

    /* footer / demo hint */
    .demo-credentials {
      margin-top: 28px;
      background: #f1f7fc;
      border-radius: 20px;
      padding: 14px 20px;
      text-align: center;
      font-size: 0.8rem;
      color: #2c5a66;
      border: 1px dashed #bdd3db;
    }
    .demo-credentials span {
      font-weight: 700;
      font-family: monospace;
      background: #e2edf2;
      padding: 4px 10px;
      border-radius: 40px;
      margin: 0 4px;
      font-size: 0.8rem;
    }
    .demo-credentials i {
      margin-right: 6px;
    }

    /* responsive */
    @media (max-width: 520px) {
      .form-area {
        padding: 28px 24px;
      }
      .brand-header {
        padding: 24px 24px 20px;
      }
      .brand-header h1 {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>


  <div class="login-container">
    <div class="login-card">
      <div class="brand-header">
        <div class="logo-icon">
          <i class="fas fa-globe"></i>
        </div>
        <h1>KHO<span style="font-weight:300"> Social</span></h1>
        <p>Domain & Hosting Admin Suite</p>
      </div>

      <div class="form-area">
        

        <!-- Error alert (hidden by default) -->
        <div id="errorAlert" class="error-message hidden">
          <i class="fas fa-exclamation-triangle"></i>
          <span id="errorText">Invalid credentials. Please try again.</span>
        </div>

        <form id="loginForm" action="javascript:void(0);">
          <div class="input-group">
            <label><i class="far fa-envelope"></i> Email Address</label>
            <div class="input-wrapper">
              <i class="fas fa-user-circle"></i>
              <input type="text" id="username" autocomplete="username" value="">
            </div>
          </div>

          <div class="input-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <div class="input-wrapper">
              <i class="fas fa-key"></i>
              <input type="password" id="password" autocomplete="current-password">
              <i class="fas fa-eye-slash toggle-password" id="togglePasswordIcon"></i>
            </div>
          </div>
          <p style="margin-bottom:10px;">
            admin@amchost.com / Admin@2026
          </p>
          <button type="submit" class="login-btn" id="loginBtn">
            <i class="fas fa-sign-in-alt"></i> Sign in to Dashboard
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    (function() {
      // DOM elements
      const usernameInput = document.getElementById('username');
      const passwordInput = document.getElementById('password');
      const loginBtn = document.getElementById('loginBtn');
      const errorAlert = document.getElementById('errorAlert');
      const errorTextSpan = document.getElementById('errorText');
      const togglePasswordIcon = document.getElementById('togglePasswordIcon');
      const loginForm = document.getElementById('loginForm');

      // ---------- Predefined valid admin credentials (demo) ----------
      // You can easily extend: multiple admin accounts for AMC / domain admin
      const VALID_CREDENTIALS = [
        { email: "admin@amchost.com", password: "Admin@2026", role: "Super Admin" },
        { email: "superadmin", password: "dashboard", role: "Super Admin" },
        { email: "amc@nexthost.com", password: "dashboard", role: "AMC Manager" },
        { email: "hosting.admin@amchost.com", password: "Host2026", role: "Hosting Admin" },
        { email: "domains@amchost.com", password: "domain123", role: "Domain Manager" }
      ];

      // helper: show error message
      function showError(message) {
        errorTextSpan.innerText = message;
        errorAlert.classList.remove('hidden');
        // auto hide after 3.5 seconds
        setTimeout(() => {
          if (errorAlert && !errorAlert.classList.contains('hidden')) {
            errorAlert.classList.add('hidden');
          }
        }, 3800);
      }

      function hideError() {
        errorAlert.classList.add('hidden');
      }

      // validate credentials
      function validateLogin(usernameVal, passwordVal) {
        if (!usernameVal || !passwordVal) {
          showError("Please enter both email/username and password.");
          return false;
        }
        // check against our credential store (case-insensitive email/username)
        const matchedUser = VALID_CREDENTIALS.find(cred => 
          cred.email.toLowerCase() === usernameVal.trim().toLowerCase() && cred.password === passwordVal
        );
        if (matchedUser) {
          return true;
        }
        // additional fallback: if any non-empty and password is "admin123"? no, stricter demo.
        showError("Invalid credentials. Access denied. Use demo credentials shown below.");
        return false;
      }

      // On successful login: store session & redirect to dashboard (admin dashboard from previous context)
      function performLoginRedirect() {
        // store login state in localStorage for demo
        localStorage.setItem('amc_admin_logged', 'true');
        localStorage.setItem('amc_admin_email', usernameInput.value.trim());
       
        window.location.href = "./dashboard.php"; 
        setTimeout(() => {
          console.log("Redirecting to Admin Dashboard...");
        }, 100);
      }

      // handle login submission
      function handleLogin(e) {
        if (e) e.preventDefault();
        hideError();
        
        const username = usernameInput.value;
        const password = passwordInput.value;
        
        // basic trim
        const trimmedUser = username.trim();
        const trimmedPass = password;
        
        if (validateLogin(trimmedUser, trimmedPass)) {
          // success login
          performLoginRedirect();
        } else {
          // error already shown by validateLogin
          // Shake effect on card optional: add class
          const card = document.querySelector('.login-card');
          card.style.transform = 'scale(1.01)';
          setTimeout(() => { if(card) card.style.transform = ''; }, 200);
        }
      }
      
      // Toggle password visibility
      function togglePassword() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePasswordIcon.classList.toggle('fa-eye-slash');
        togglePasswordIcon.classList.toggle('fa-eye');
      }
      
      // Pre-fill demo credentials quickly? optional helper: but we can add quick fill feature? Not needed
      function setDemoCredentials() {
        // This is optional: double click on logo area? but provide via demo hint, but for convenience, we can allow quick fill
        // We'll just leave it as manual typing.
      }
      
      // Check if already logged in (optional: auto redirect to dashboard if session exists)
      function checkExistingSession() {
        const loggedLocal = localStorage.getItem('amc_admin_logged');
        const loggedSession = sessionStorage.getItem('amc_admin_logged');
        if (loggedLocal === 'true' || loggedSession === 'true') {
          // If already authenticated, we could auto-redirect to dashboard (better UX)
          // but avoid loops, add a flag to prevent infinite redirect on login page
          const urlParams = new URLSearchParams(window.location.search);
          if (!urlParams.has('redirected')) {
            window.location.href = "./dashboard.php?redirected=true";
          }
        }
      }
      
      // Event listeners
      loginForm.addEventListener('submit', handleLogin);
      loginBtn.addEventListener('click', handleLogin);
      togglePasswordIcon.addEventListener('click', togglePassword);
      
      // additional: pressing Enter inside form triggers submit (already via form)
      
      // optional: set some demo autocomplete / sample
      usernameInput.addEventListener('focus', () => hideError());
      passwordInput.addEventListener('focus', () => hideError());
      
      // prefill demo username for easier testing (not pre-filled but placeholder guides)
      // For better usability, we can prefill the demo admin email as placeholder
      usernameInput.setAttribute('placeholder', 'admin@amchost.com');
      passwordInput.setAttribute('placeholder', 'Admin@2026');
      
      // Add small helper: on page load, set focus to username
      usernameInput.focus();
      
      // check existing session but we avoid immediate redirect so user can re-login if needed, but provide explicit redirect check
      // checkExistingSession(); // optional, but might cause conflict if dashboard not ready; but we comment because dashboard may not exist yet.
      // Better not auto redirect, let user click login.
      
      // Add additional demo for password recovery style
      console.log("AMC Login UI ready — use admin@amchost.com / Admin@2026");
    })();
  </script>
</body>
</html>
