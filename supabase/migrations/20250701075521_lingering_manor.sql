-- Snippet Manager Database Schema
CREATE DATABASE IF NOT EXISTS snippet_manager;
USE snippet_manager;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    avatar VARCHAR(255),
    theme_preference ENUM('light', 'dark') DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6',
    icon VARCHAR(50) DEFAULT 'fas fa-folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    extension VARCHAR(10),
    color VARCHAR(7),
    prism_class VARCHAR(20)
);

-- Snippets table
CREATE TABLE IF NOT EXISTS snippets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    code LONGTEXT NOT NULL,
    language_id INT,
    category_id INT,
    user_id INT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    is_favorite BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FULLTEXT(title, description, code)
);

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#6B7280',
    usage_count INT DEFAULT 0
);

-- Snippet Tags junction table
CREATE TABLE IF NOT EXISTS snippet_tags (
    snippet_id INT,
    tag_id INT,
    PRIMARY KEY (snippet_id, tag_id),
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Snippet Views table for analytics
CREATE TABLE IF NOT EXISTS snippet_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    snippet_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    snippet_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, snippet_id)
);

-- Insert default languages
INSERT INTO languages (name, extension, color, prism_class) VALUES
('JavaScript', 'js', '#F7DF1E', 'javascript'),
('TypeScript', 'ts', '#3178C6', 'typescript'),
('Python', 'py', '#3776AB', 'python'),
('HTML', 'html', '#E34F26', 'markup'),
('CSS', 'css', '#1572B6', 'css'),
('SQL', 'sql', '#4479A1', 'sql'),
('PHP', 'php', '#777BB4', 'php'),
('Java', 'java', '#ED8B00', 'java'),
('C#', 'cs', '#239120', 'csharp'),
('C++', 'cpp', '#00599C', 'cpp'),
('Go', 'go', '#00ADD8', 'go'),
('Rust', 'rs', '#000000', 'rust'),
('Ruby', 'rb', '#CC342D', 'ruby');

-- Insert default categories
INSERT INTO categories (name, description, color, icon) VALUES
('Web Development', 'Frontend and backend web development snippets', '#3B82F6', 'fas fa-globe'),
('Algorithms', 'Data structures and algorithms', '#10B981', 'fas fa-code-branch'),
('Database', 'SQL queries and database operations', '#8B5CF6', 'fas fa-database'),
('Utilities', 'Helper functions and utilities', '#F59E0B', 'fas fa-tools'),
('API', 'REST API and GraphQL snippets', '#EF4444', 'fas fa-plug'),
('Authentication', 'Login, registration, and security', '#6366F1', 'fas fa-lock'),
('UI Components', 'Reusable UI components', '#EC4899', 'fas fa-palette'),
('Testing', 'Unit tests and integration tests', '#14B8A6', 'fas fa-vial');

-- Insert default tags
INSERT INTO tags (name, color) VALUES
('React', '#61DAFB'),
('Vue.js', '#4FC08D'),
('Angular', '#DD0031'),
('Node.js', '#339933'),
('Express', '#000000'),
('Laravel', '#FF2D20'),
('Django', '#092E20'),
('Flask', '#000000'),
('jQuery', '#0769AD'),
('Bootstrap', '#7952B3'),
('Tailwind', '#06B6D4'),
('MongoDB', '#47A248'),
('MySQL', '#00000F'),
('PostgreSQL', '#336791'),
('Redis', '#DC382D'),
('Docker', '#2496ED'),
('Git', '#F05032'),
('AWS', '#FF9900'),
('Responsive', '#FF6B6B'),
('Animation', '#4ECDC4');

-- Insert default snippets
INSERT INTO snippets (title, description, code, language_id, category_id, user_id, is_public, view_count, created_at) VALUES
('Fibonacci Hesaplama (Python)', 'Girilen n sayısına kadar Fibonacci dizisini hesaplar.', 'def fibonacci(n):\n    a, b = 0, 1\n    for _ in range(n):\n        print(a)\n        a, b = b, a + b', 3, 1, 1, 1, 12, NOW()),
('Dosya Okuma (Python)', 'Bir dosyayı satır satır okur.', 'with open("dosya.txt", "r") as f:\n    for satir in f:\n        print(satir.strip())', 3, 1, 1, 1, 7, NOW()),
('Dizideki En Büyük Sayı (JavaScript)', 'Bir dizideki en büyük sayıyı bulur.', 'const arr = [1, 5, 2, 9, 3];\nconst max = Math.max(...arr);\nconsole.log(max);', 1, 1, 1, 1, 15, NOW()),
('Asenkron Veri Çekme (JavaScript)', 'fetch API ile veri çeker.', 'fetch("https://jsonplaceholder.typicode.com/posts/1")\n  .then(res => res.json())\n  .then(data => console.log(data));', 1, 1, 1, 1, 8, NOW()),
('Responsive Görsel (HTML)', 'Responsive bir görsel etiketi örneği.', '<img src="resim.jpg" alt="Açıklama" style="max-width:100%;height:auto;">', 4, 1, 1, 1, 4, NOW()),
('Basit Form (HTML)', 'Kullanıcıdan veri alan basit bir form.', '<form>\n  <input type="text" name="ad" placeholder="Adınız">\n  <button type="submit">Gönder</button>\n</form>', 4, 1, 1, 1, 6, NOW()),
('Toplam Kayıt Sayısı (SQL)', 'Bir tablodaki toplam kayıt sayısını döndürür.', 'SELECT COUNT(*) FROM kullanicilar;', 6, 1, 1, 1, 10, NOW()),
('Belirli Şartla Seçim (SQL)', 'Yaşı 18''den büyük kullanıcıları seçer.', 'SELECT * FROM kullanicilar WHERE yas > 18;', 6, 1, 1, 1, 5, NOW()),
('POST Kontrolü (PHP)', 'Formdan gelen POST verisini kontrol eder.', '<?php\nif ($_SERVER["REQUEST_METHOD"] == "POST") {\n    echo $_POST["ad"];\n}\n?>', 7, 1, 1, 1, 9, NOW()),
('Basit Sınıf (PHP)', 'Bir sınıf ve nesne örneği.', '<?php\nclass Araba {\n    public $renk;\n    function __construct($renk) {\n        $this->renk = $renk;\n    }\n}\n$araba = new Araba("kırmızı");\necho $araba->renk;\n?>', 7, 1, 1, 1, 3, NOW()),
('Dizi Toplamı (C#)', 'Bir dizideki sayıların toplamını hesaplar.', 'int[] sayilar = {1, 2, 3, 4, 5};\nint toplam = sayilar.Sum();\nConsole.WriteLine(toplam);', 9, 1, 1, 1, 2, NOW()),
('Ters Çevirme (Java)', 'Bir stringi ters çevirir.', 'String s = "merhaba";\nString ters = new StringBuilder(s).reverse().toString();\nSystem.out.println(ters);', 8, 1, 1, 1, 6, NOW()),
('Hello World (Go)', 'Go dilinde ekrana yazı yazdırma.', 'package main\nimport "fmt"\nfunc main() {\n    fmt.Println("Hello, World!")\n}', 11, 1, 1, 1, 1, NOW()),
('Vektör Toplamı (Rust)', 'Bir vektördeki sayıların toplamını bulur.', 'let v = vec![1, 2, 3, 4, 5];\nlet toplam: i32 = v.iter().sum();\nprintln!("{}", toplam);', 12, 1, 1, 1, 1, NOW()),
('Dizi Elemanlarını Yazdır (Ruby)', 'Bir dizinin elemanlarını ekrana yazdırır.', 'arr = [1, 2, 3, 4, 5]\narr.each { |x| puts x }', 13, 1, 1, 1, 1, NOW());

-- Create indexes for better performance
CREATE INDEX idx_snippets_user_id ON snippets(user_id);
CREATE INDEX idx_snippets_category_id ON snippets(category_id);
CREATE INDEX idx_snippets_language_id ON snippets(language_id);
CREATE INDEX idx_snippets_created_at ON snippets(created_at);
CREATE INDEX idx_snippet_views_snippet_id ON snippet_views(snippet_id);
CREATE INDEX idx_snippet_views_viewed_at ON snippet_views(viewed_at);

-- Örnek kullanıcı ekle
INSERT INTO users (username, email, password, first_name, last_name, is_active)
VALUES ('admin', 'admin@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'Admin', 'Kullanıcı', 1);