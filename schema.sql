-- Smart Resume Analyzer Database Schema
-- MySQL 8.0+

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255),
    phone VARCHAR(20),
    bio TEXT,
    location VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    remember_token VARCHAR(255),
    last_login TIMESTAMP NULL,
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resumes table
CREATE TABLE IF NOT EXISTS resumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    resume_text LONGTEXT NOT NULL,
    file_size INT,
    file_hash VARCHAR(64) UNIQUE,
    parsed_at TIMESTAMP NULL,
    is_parsed BOOLEAN DEFAULT FALSE,
    parsing_error TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_uploaded_at (uploaded_at),
    INDEX idx_file_hash (file_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analysis results table
CREATE TABLE IF NOT EXISTS analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    user_id INT NOT NULL,
    job_description TEXT NOT NULL,
    match_score DECIMAL(5,2),
    ats_score DECIMAL(5,2),
    matched_skills JSON,
    missing_skills JSON,
    suggestions JSON,
    skill_gap_analysis JSON,
    analysis_notes TEXT,
    nlp_model VARCHAR(100),
    api_used VARCHAR(50),
    tokens_used INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_resume_id (resume_id),
    INDEX idx_created_at (created_at),
    INDEX idx_match_score (match_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detected skills table
CREATE TABLE IF NOT EXISTS detected_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    skill_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    proficiency_level VARCHAR(50),
    frequency INT DEFAULT 1,
    confidence_score DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
    INDEX idx_resume_id (resume_id),
    INDEX idx_skill_name (skill_name),
    INDEX idx_category (category),
    UNIQUE KEY unique_resume_skill (resume_id, skill_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skill taxonomy (reference table)
CREATE TABLE IF NOT EXISTS skill_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(255) UNIQUE NOT NULL,
    category VARCHAR(100) NOT NULL,
    aliases JSON,
    level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_skill_name (skill_name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics table
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_type VARCHAR(100) NOT NULL,
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API usage tracking
CREATE TABLE IF NOT EXISTS api_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_provider VARCHAR(50),
    endpoint VARCHAR(255),
    tokens_used INT,
    cost DECIMAL(8,4),
    response_time INT,
    status_code INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_api_provider (api_provider),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type VARCHAR(50),
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert predefined skill categories
INSERT IGNORE INTO skill_categories (skill_name, category, aliases, level) VALUES
('PHP', 'Backend', '["php", "php8", "php7"]', 3),
('Python', 'Backend', '["python", "py", "python3"]', 3),
('Java', 'Backend', '["java", "j2ee", "spring"]', 3),
('JavaScript', 'Frontend', '["js", "javascript", "es6", "es5"]', 3),
('TypeScript', 'Frontend', '["typescript", "ts"]', 2),
('React', 'Frontend', '["react", "reactjs", "react.js"]', 3),
('Angular', 'Frontend', '["angular", "angularjs", "ng"]', 3),
('Vue.js', 'Frontend', '["vue", "vuejs", "vue.js"]', 2),
('Node.js', 'Backend', '["nodejs", "node.js", "node"]', 3),
('Express', 'Framework', '["express", "expressjs"]', 2),
('Laravel', 'Framework', '["laravel", "lumen"]', 2),
('Django', 'Framework', '["django", "django-rest"]', 2),
('Spring', 'Framework', '["spring", "spring-boot"]', 2),
('MySQL', 'Database', '["mysql", "mariadb"]', 3),
('PostgreSQL', 'Database', '["postgresql", "postgres", "psql"]', 3),
('MongoDB', 'Database', '["mongodb", "mongo"]', 2),
('Redis', 'Cache', '["redis", "memcached"]', 2),
('Docker', 'DevOps', '["docker", "containers"]', 2),
('Kubernetes', 'DevOps', '["kubernetes", "k8s"]', 2),
('AWS', 'Cloud', '["aws", "amazon"]', 2),
('Azure', 'Cloud', '["azure", "microsoft"]', 2),
('GCP', 'Cloud', '["gcp", "google-cloud"]', 2),
('Git', 'VCS', '["git", "github", "gitlab", "bitbucket"]', 3),
('SQL', 'Database', '["sql", "t-sql", "pl-sql"]', 3),
('HTML', 'Frontend', '["html", "html5"]', 3),
('CSS', 'Frontend', '["css", "css3", "scss", "sass"]', 3),
('REST API', 'API', '["rest", "restful"]', 2),
('GraphQL', 'API', '["graphql"]', 2),
('CI/CD', 'DevOps', '["ci/cd", "jenkins", "gitlab-ci", "github-actions"]', 2),
('Linux', 'System', '["linux", "ubuntu", "centos"]', 2),
('Windows', 'System', '["windows", "powershell"]', 1),
('Agile', 'Methodology', '["agile", "scrum", "kanban"]', 1),
('Testing', 'QA', '["testing", "junit", "pytest", "jest"]', 2),
('Microservices', 'Architecture', '["microservices"]', 1),
('Machine Learning', 'AI', '["machine-learning", "ml", "tensorflow", "pytorch"]', 1),
('Data Analysis', 'Data', '["data-analysis", "pandas", "numpy"]', 1),
('Problem Solving', 'Soft Skills', '["problem-solving"]', 1),
('Communication', 'Soft Skills', '["communication", "presentation"]', 1),
('Leadership', 'Soft Skills', '["leadership", "management"]', 1),
('Team Work', 'Soft Skills', '["teamwork", "collaboration"]', 1);

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'Smart Resume Analyzer', 'string', 'Application name'),
('app_version', '1.0.0', 'string', 'Application version'),
('max_resume_size', '5242880', 'number', 'Maximum resume file size in bytes (5MB)'),
('max_resumes_per_user', '10', 'number', 'Maximum number of resumes per user'),
('nlp_api', 'huggingface', 'string', 'NLP API provider (huggingface or openai)'),
('enable_email_verification', 'true', 'boolean', 'Enable email verification'),
('enable_password_reset', 'true', 'boolean', 'Enable password reset'),
('results_per_page', '10', 'number', 'Results per page in pagination'),
('smtp_enabled', 'false', 'boolean', 'Enable SMTP email'),
('cache_duration', '3600', 'number', 'Cache duration in seconds'),
('allow_registration', 'true', 'boolean', 'Allow user registration');
