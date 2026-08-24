# AI Study Assistant

PHP + MySQL (or SQLite) + HTML/CSS/JS frontend with Google Gemini AI.

## Quick Start (XAMPP / WAMP)

1. Copy the `ai-study-assistant/` folder to:
   - XAMPP → `C:\xampp\htdocs\ai-study-assistant`
   - WAMP  → `C:\wamp64\www\ai-study-assistant`

2. Start Apache + MySQL from control panel.

3. Open phpMyAdmin → Import `database.sql` → click Go.

4. Open `api/config.php` and:
   - Paste your free Gemini API key from: https://aistudio.google.com/app/apikey
   - Update DB_PASS if your MySQL has a password (XAMPP default is blank)

5. Visit: `http://localhost/ai-study-assistant/`

---

## Quick Demo (No MySQL needed)

The app automatically uses SQLite when MySQL is not available.

```bash
cd ai-study-assistant
php -S localhost:8000 router.php
```

Open: `http://localhost:8000/`

---

## Getting a Gemini API Key (Free)

1. Go to: https://aistudio.google.com/app/apikey
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the key
5. Paste it in `api/config.php` → `GEMINI_API_KEY`

---

## API Endpoints

| Method | URL                   | Purpose                        |
|--------|-----------------------|--------------------------------|
| POST   | `api/auth.php`        | Login / Signup                 |
| POST   | `api/notes.php`       | Generate AI study notes        |
| POST   | `api/quiz.php`        | Generate AI multiple-choice quiz |
| POST   | `api/chat.php`        | AI chat assistant              |
| POST   | `api/contact.php`     | Save contact form              |
| POST   | `api/newsletter.php`  | Subscribe to newsletter        |
| GET    | `api/health.php`      | Health check                   |

---

## Troubleshooting

- **API key error** → Make sure you pasted the key in `api/config.php`
- **Database error with XAMPP** → Import `database.sql` in phpMyAdmin
- **Blank AI responses** → Check your Gemini API key is valid
- **PHP not running** → Make sure Apache is started in XAMPP/WAMP
