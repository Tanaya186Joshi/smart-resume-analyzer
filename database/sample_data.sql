-- Smart Resume Analyzer - Sample Data
-- Use for testing and development

-- Insert demo users
INSERT INTO users (name, email, password, role, is_active, email_verified) VALUES
('John Doe', 'john@example.com', '$2y$12$...', 'user', 1, 1),
('Jane Smith', 'jane@example.com', '$2y$12$...', 'user', 1, 1),
('Admin User', 'admin@example.com', '$2y$12$...', 'admin', 1, 1);

-- Insert sample resumes (these would normally have actual file content)
INSERT INTO resumes (user_id, filename, original_filename, resume_text, file_size, is_parsed) VALUES
(1, 'resume_1234567890_12345.pdf', 'john_doe_resume.pdf', 
'John Doe
Senior Software Engineer
Email: john@example.com | Phone: (555) 123-4567

SUMMARY
Experienced Software Engineer with 5+ years of experience in full-stack development.
Proficient in PHP, Python, JavaScript, React, and Node.js.

EXPERIENCE
Senior Developer - Tech Company (2020-Present)
- Developed and maintained multiple web applications using PHP and React
- Implemented RESTful APIs with Laravel framework
- Led team of 3 developers on critical projects
- Improved application performance by 40%

Junior Developer - StartUp Inc (2019-2020)
- Built responsive web applications using Bootstrap and jQuery
- Worked with MySQL databases
- Implemented CI/CD pipelines using GitHub Actions

SKILLS
Languages: PHP, Python, JavaScript, Java, SQL
Frontend: React, Angular, Vue.js, HTML5, CSS3
Backend: Node.js, Laravel, Django, Express
Databases: MySQL, PostgreSQL, MongoDB, Redis
Tools: Git, Docker, Jenkins, AWS

EDUCATION
Bachelor of Science in Computer Science
University Name (2019)', 85000, 1);

-- Insert sample analysis results
INSERT INTO analysis (resume_id, user_id, job_description, match_score, ats_score, matched_skills, missing_skills, nlp_model) VALUES
(1, 1, 
'Senior Full Stack Developer
Requirements:
- 5+ years of software development experience
- Strong knowledge of PHP and JavaScript
- Experience with React and Node.js
- Database design and optimization
- REST API development
- Team leadership experience
- CI/CD pipeline knowledge',
85.50, 88.25, 
'{"PHP":"Backend","JavaScript":"Frontend","React":"Frontend","Node.js":"Backend","MySQL":"Database","Git":"VCS","Docker":"DevOps"}',
'{"Python":"Backend","GraphQL":"API","Kubernetes":"DevOps"}',
'local-nlp');

-- Insert skill categories (these should already exist from schema)
-- This is just to ensure some key ones are present

-- Insert analytics events
INSERT INTO analytics (user_id, event_type, event_data) VALUES
(1, 'user_registered', '{"email":"john@example.com"}'),
(1, 'resume_uploaded', '{"filename":"john_doe_resume.pdf","size":85000}'),
(1, 'analysis_created', '{"resume_id":1,"match_score":85.50}'),
(2, 'user_registered', '{"email":"jane@example.com"}'),
(3, 'user_logged_in', '{"email":"admin@example.com"}');

-- Update user last login times
UPDATE users SET last_login = NOW() - INTERVAL 2 HOUR WHERE id = 1;
UPDATE users SET last_login = NOW() - INTERVAL 1 DAY WHERE id = 2;
UPDATE users SET last_login = NOW() WHERE id = 3;

-- Note: Replace $2y$12$... with actual bcrypt hashes
-- To generate: password_hash('password', PASSWORD_BCRYPT, ['cost' => 12])
-- Demo password: "Demo@12345"
