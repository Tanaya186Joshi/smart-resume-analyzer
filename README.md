# Smart Resume Analyzer 🚀

An AI-powered resume analysis and ATS (Applicant Tracking System) matching web application that helps job seekers optimize their resumes and improve their chances of landing interviews.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/php-8.0%2B-blue.svg)
![MySQL](https://img.shields.io/badge/mysql-8.0%2B-blue.svg)
![Bootstrap](https://img.shields.io/badge/bootstrap-5.3-purple.svg)

## 🌟 Features

### Core Functionality
- ✅ **Resume Upload** - Secure PDF resume uploads with validation
- ✅ **PDF Parsing** - Automatic text extraction from PDF resumes
- ✅ **Skill Detection** - AI-powered skill extraction and categorization
- ✅ **Job Matching** - Compare resume against job descriptions
- ✅ **ATS Score** - Calculate resume compatibility with ATS systems
- ✅ **Gap Analysis** - Identify missing skills and requirements
- ✅ **Suggestions** - Receive actionable improvement recommendations
- ✅ **Analysis History** - Track all previous analyses

### User Management
- ✅ **User Registration** - Create new accounts with email verification
- ✅ **Secure Authentication** - Password hashing with BCRYPT
- ✅ **Password Recovery** - Reset forgotten passwords
- ✅ **Session Management** - Secure session handling with validation
- ✅ **Remember Me** - Optional 30-day login persistence
- ✅ **Profile Management** - Update user information

### Admin Features
- ✅ **Dashboard Analytics** - Monitor system usage and statistics
- ✅ **User Management** - View and manage user accounts
- ✅ **System Logs** - Track all user activities
- ✅ **Performance Metrics** - Monitor application performance

### Technical Features
- ✅ **Responsive Design** - Works on all devices (mobile, tablet, desktop)
- ✅ **Modern UI** - LinkedIn + Notion + GitHub inspired design
- ✅ **API-First Architecture** - RESTful APIs for all operations
- ✅ **Database Optimization** - Indexed queries and efficient data storage
- ✅ **Error Handling** - Comprehensive error handling and logging
- ✅ **Security** - CSRF protection, input validation, SQL injection prevention

## 📋 System Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer (for dependency management)
- 100MB free disk space
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Optional
- HuggingFace or OpenAI API key (for enhanced NLP features)
- pdftotext utility (for enhanced PDF processing)

## 🚀 Installation Guide

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/smart-resume-analyzer.git
cd smart-resume-analyzer
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Create Database
```bash
mysql -u root -p
CREATE DATABASE resume_analyzer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Import Schema
```bash
mysql -u root -p resume_analyzer < database/schema.sql
```

### 5. Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with your configuration:
```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=resume_analyzer

# Application
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=true

# NLP API
NLP_API_PROVIDER=huggingface
HUGGINGFACE_API_KEY=your_key_here

# Email (Optional)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### 6. Set Permissions
```bash
chmod -R 755 uploads/
chmod -R 755 logs/
```

### 7. Create Upload Directory
```bash
mkdir -p uploads
mkdir -p logs
```

### 8. Start Development Server
```bash
php -S localhost:8000
```

Visit: http://localhost:8000

## 📁 Project Structure

```
smart-resume-analyzer/
├── index.php                 # Landing page
├── composer.json            # PHP dependencies
├── .env.example            # Environment configuration template
├── .env                     # Environment configuration (create from example)
│
├── api/
│   ├── extract.php         # Resume PDF extraction API
│   └── compare.php         # Resume vs job comparison API
│
├── auth/
│   └── logout.php          # Logout handler
│
├── admin/
│   ├── dashboard.php       # Admin analytics dashboard
│   ├── users.php          # User management
│   ├── analytics.php      # Detailed analytics
│   └── settings.php       # System settings
│
├── config/
│   ├── database.php        # Database connection & queries
│   ├── auth.php           # Authentication & session management
│   └── helpers.php        # Utility functions
│
├── pages/
│   ├── login.php          # User login page
│   ├── register.php       # User registration page
│   ├── dashboard.php      # Main dashboard
│   ├── upload.php         # Resume upload & analysis page
│   ├── results.php        # Analysis results display
│   ├── history.php        # Analysis history
│   └── profile.php        # User profile
│
├── includes/
│   ├── header.php         # HTML header & meta tags
│   ├── navbar.php         # Navigation bar
│   └── footer.php         # HTML footer & scripts
│
├── assets/
│   ├── css/
│   │   ├── style.css      # Global styles
│   │   ├── dashboard.css  # Dashboard styles
│   │   └── landing.css    # Landing page styles
│   ├── js/
│   │   ├── main.js        # Main JavaScript
│   │   ├── dashboard.js   # Dashboard scripts
│   │   └── upload.js      # Upload functionality
│   └── images/            # Static images
│
├── uploads/               # User uploaded resumes (create this)
├── logs/                 # Application logs (create this)
├── database/
│   ├── schema.sql        # Database schema
│   └── sample_data.sql   # Sample data
│
├── docs/                 # Documentation
└── README.md            # This file
```

## 🔐 Security Features

- **Password Hashing**: BCRYPT with cost factor 12
- **Session Management**: Secure session validation with IP and User-Agent checks
- **CSRF Protection**: Token-based CSRF prevention
- **SQL Injection Prevention**: Parameterized queries using MySQLi prepared statements
- **XSS Protection**: Output escaping and HTML sanitization
- **File Upload Validation**: MIME type and size validation
- **Rate Limiting**: Configurable rate limiting for API endpoints
- **Email Verification**: Optional email verification for accounts
- **Password Reset**: Secure time-limited reset tokens

## 🗄️ Database Schema

### Key Tables
- **users** - User accounts and profiles
- **resumes** - Uploaded resume files
- **analysis** - Resume analysis results
- **detected_skills** - Skills extracted from resumes
- **skill_categories** - Skill taxonomy and aliases
- **analytics** - System event logging
- **api_usage** - API call tracking and analytics

## 📊 API Endpoints

### Extract API (`/api/extract.php`)
- **POST** - Upload resume
  ```
  Parameters: resume (file), csrf_token
  Returns: resume_id, filename, skills_detected
  ```

- **GET** - Fetch resumes
  ```
  Parameters: id (optional)
  Returns: resumes list or specific resume details
  ```

### Compare API (`/api/compare.php`)
- **POST** - Analyze resume vs job description
  ```
  Body: {resume_id, job_description}
  Returns: match_score, ats_score, matched_skills, missing_skills, suggestions
  ```

## 🎨 UI/UX Features

- **Modern Design** - Clean, professional interface inspired by LinkedIn, Notion, and GitHub
- **Responsive Layout** - Mobile-first approach with Bootstrap 5
- **Smooth Animations** - AOS (Animate On Scroll) for engaging transitions
- **Dark Mode Ready** - CSS variables for easy theme switching
- **Accessibility** - WCAG 2.1 AA compliant
- **Performance Optimized** - Lazy loading, code splitting, minified assets

## 📝 Usage Examples

### For End Users
1. Register account or login
2. Upload PDF resume
3. Paste job description
4. Get instant analysis with:
   - Match percentage
   - ATS compatibility score
   - Matched/missing skills
   - Improvement suggestions
5. View analysis history
6. Compare multiple resumes

### For Developers
```php
// Extract skills from resume
$resumeText = file_get_contents('resume.txt');
$skills = extractSkillsFromText($resumeText, $db);

// Perform analysis
$result = performAnalysis(
    $resume['resume_text'],
    $jobDescription,
    $db
);

// Access results
echo "Match Score: " . $result['match_score'];
echo "ATS Score: " . $result['ats_score'];
```

## 🔧 Configuration

### Environment Variables
```env
# Core
APP_NAME=Smart Resume Analyzer
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.com

# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=password
DB_NAME=resume_analyzer

# NLP
NLP_API_PROVIDER=huggingface
HUGGINGFACE_API_KEY=your_key
OPENAI_API_KEY=your_key

# Security
SESSION_LIFETIME=120
CSRF_TOKEN_LENGTH=32
PASSWORD_RESET_EXPIRY=3600

# File Upload
MAX_RESUME_SIZE=5242880
UPLOAD_DIRECTORY=./uploads

# Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
```

## 🐛 Troubleshooting

### Database Connection Issues
```bash
# Check MySQL is running
mysql -u root -p

# Verify credentials in .env
# Check database exists: SHOW DATABASES;
```

### PDF Extraction Not Working
```bash
# Install pdftotext
sudo apt-get install poppler-utils

# Or configure Composer to use smalot/pdfparser
composer require smalot/pdfparser
```

### Permission Errors
```bash
chmod -R 755 uploads/
chmod -R 755 logs/
```

### Session Issues
```php
// Clear sessions
php -r "session_start(); session_destroy();"

// Check php.ini settings
php -i | grep session
```

## 📚 API Documentation

Full API documentation available in `/docs/API.md`

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Bootstrap 5 for responsive UI
- Font Awesome for icons
- AOS for scroll animations
- Chart.js for data visualization
- HuggingFace for NLP models
- All contributors and testers

## 📞 Support

- **Issues**: GitHub Issues for bug reports
- **Email**: support@resumeanalyzer.local
- **Documentation**: Full docs in `/docs` folder

## 🚀 Roadmap

- [ ] Multi-file resume comparison
- [ ] DOCX format support
- [ ] LinkedIn integration
- [ ] Batch analysis
- [ ] Mobile app
- [ ] Interview preparation
- [ ] Resume template builder
- [ ] Premium features

## 📊 Performance Metrics

- **Average Response Time**: < 500ms
- **PDF Processing**: < 2 seconds (1-2 page)
- **Database Queries**: Optimized with indexes
- **Frontend Load**: < 2 seconds (first paint)

## 🔒 Privacy & Data Protection

- No data sharing with third parties
- Encrypted file storage
- Automatic data cleanup (30-day retention)
- GDPR compliant
- Privacy policy available

---

**Version**: 1.0.0  
**Last Updated**: August 2024  
**Status**: Production Ready ✅
