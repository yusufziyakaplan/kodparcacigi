-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 02 Tem 2025, 06:23:24
-- Sunucu sürümü: 8.4.3
-- PHP Sürümü: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `snippet_manager`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `color`, `icon`, `user_id`) VALUES
(1, 'Genel', 'Genel kod parçacıkları', '#6c757d', 'fa-solid fa-folder-open', NULL),
(2, 'Web Development', 'Frontend and backend web development snippets', '#3B82F6', 'fa-solid fa-code', NULL),
(3, 'Algorithms', 'Data structures and algorithms', '#10B981', 'fa-solid fa-code', NULL),
(4, 'Database', 'SQL queries and database operations', '#8B5CF6', 'fa-solid fa-code', NULL),
(5, 'Utilities', 'Helper functions and utilities', '#F59E0B', 'fa-solid fa-code', NULL),
(6, 'API', 'REST API and GraphQL snippets', '#EF4444', 'fa-solid fa-code', NULL),
(7, 'Authentication', 'Login, registration, and security', '#6366F1', 'fa-solid fa-code', NULL),
(8, 'UI Components', 'Reusable UI components', '#EC4899', 'fa-solid fa-code', NULL),
(9, 'Testing', 'Unit tests and integration tests', '#14B8A6', 'fa-solid fa-code', NULL),
(18, 'Masaüstü', NULL, '#0d6efd', 'fa-solid fa-code', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `favorites`
--

CREATE TABLE `favorites` (
  `snippet_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `languages`
--

CREATE TABLE `languages` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `extension` varchar(20) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `prism_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `languages`
--

INSERT INTO `languages` (`id`, `name`, `extension`, `color`, `prism_class`) VALUES
(1, 'JavaScript', 'js', '#F7DF1E', 'javascript'),
(2, 'TypeScript', 'ts', '#3178C6', 'typescript'),
(3, 'Python', 'py', '#3776AB', 'python'),
(4, 'HTML', 'html', '#E34F26', 'markup'),
(5, 'CSS', 'css', '#1572B6', 'css'),
(6, 'SQL', 'sql', '#4479A1', 'sql'),
(7, 'PHP', 'php', '#777BB4', 'php'),
(8, 'Java', 'java', '#ED8B00', 'java'),
(9, 'C#', 'cs', '#239120', 'csharp'),
(10, 'C++', 'cpp', '#00599C', 'cpp'),
(11, 'Go', 'go', '#00ADD8', 'go'),
(12, 'Rust', 'rs', '#000000', 'rust'),
(13, 'Ruby', 'rb', '#CC342D', 'ruby'),
(40, 'Vb.Net', 'vb', '#4aa324', 'vb.net');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `snippets`
--

CREATE TABLE `snippets` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `code` text NOT NULL,
  `language_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `view_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `snippets`
--

INSERT INTO `snippets` (`id`, `title`, `description`, `code`, `language_id`, `category_id`, `user_id`, `is_public`, `view_count`, `created_at`, `updated_at`) VALUES
(45, 'Fibonacci Hesaplama (Python)', 'Girilen n sayısına kadar Fibonacci dizisini hesaplar.', 'def fibonacci(n):\n    a, b = 0, 1\n    for _ in range(n):\n        print(a)\n        a, b = b, a + b', 3, 1, 1, 1, 12, '2025-07-01 11:07:16', NULL),
(46, 'Dosya Okuma (Python)', 'Bir dosyayı satır satır okur.', 'with open(\"dosya.txt\", \"r\") as f:\n    for satir in f:\n        print(satir.strip())', 3, 1, 1, 1, 7, '2025-07-01 11:07:16', NULL),
(47, 'Dizideki En Büyük Sayı (JavaScript)', 'Bir dizideki en büyük sayıyı bulur.', 'const arr = [1, 5, 2, 9, 3];\nconst max = Math.max(...arr);\nconsole.log(max);', 1, 1, 1, 1, 15, '2025-07-01 11:07:16', NULL),
(48, 'Asenkron Veri Çekme (JavaScript)', 'fetch API ile veri çeker.', 'fetch(\"https://jsonplaceholder.typicode.com/posts/1\")\n  .then(res => res.json())\n  .then(data => console.log(data));', 1, 1, 1, 1, 8, '2025-07-01 11:07:16', NULL),
(49, 'Responsive Görsel (HTML)', 'Responsive bir görsel etiketi örneği.', '<img src=\"resim.jpg\" alt=\"Açıklama\" style=\"max-width:100%;height:auto;\">', 4, 1, 1, 1, 4, '2025-07-01 11:07:16', NULL),
(50, 'Basit Form (HTML)', 'Kullanıcıdan veri alan basit bir form.', '<form>\n  <input type=\"text\" name=\"ad\" placeholder=\"Adınız\">\n  <button type=\"submit\">Gönder</button>\n</form>', 4, 1, 1, 1, 6, '2025-07-01 11:07:16', NULL),
(51, 'Toplam Kayıt Sayısı (SQL)', 'Bir tablodaki toplam kayıt sayısını döndürür.', 'SELECT COUNT(*) FROM kullanicilar;', 6, 1, 1, 1, 10, '2025-07-01 11:07:16', NULL),
(52, 'Belirli Şartla Seçim (SQL)', 'Yaşı 18\'den büyük kullanıcıları seçer.', 'SELECT * FROM kullanicilar WHERE yas > 18;', 6, 1, 1, 1, 5, '2025-07-01 11:07:16', NULL),
(53, 'POST Kontrolü (PHP)', 'Formdan gelen POST verisini kontrol eder.', '<?php\nif ($_SERVER[\"REQUEST_METHOD\"] == \"POST\") {\n    echo $_POST[\"ad\"];\n}\n?>', 7, 1, 1, 1, 9, '2025-07-01 11:07:16', NULL),
(54, 'Basit Sınıf (PHP)', 'Bir sınıf ve nesne örneği.', '<?php\nclass Araba {\n    public $renk;\n    function __construct($renk) {\n        $this->renk = $renk;\n    }\n}\n$araba = new Araba(\"kırmızı\");\necho $araba->renk;\n?>', 7, 1, 1, 1, 3, '2025-07-01 11:07:16', NULL),
(55, 'Dizi Toplamı (C#)', 'Bir dizideki sayıların toplamını hesaplar.', 'int[] sayilar = {1, 2, 3, 4, 5};\nint toplam = sayilar.Sum();\nConsole.WriteLine(toplam);', 9, 1, 1, 1, 2, '2025-07-01 11:07:16', NULL),
(56, 'Ters Çevirme (Java)', 'Bir stringi ters çevirir.', 'String s = \"merhaba\";\nString ters = new StringBuilder(s).reverse().toString();\nSystem.out.println(ters);', 8, 1, 1, 1, 6, '2025-07-01 11:07:16', NULL),
(57, 'Hello World (Go)', 'Go dilinde ekrana yazı yazdırma.', 'package main\nimport \"fmt\"\nfunc main() {\n    fmt.Println(\"Hello, World!\")\n}', 11, 1, 1, 1, 1, '2025-07-01 11:07:16', NULL),
(58, 'Vektör Toplamı (Rust)', 'Bir vektördeki sayıların toplamını bulur.', 'let v = vec![1, 2, 3, 4, 5];\nlet toplam: i32 = v.iter().sum();\nprintln!(\"{}\", toplam);', 12, 1, 1, 1, 1, '2025-07-01 11:07:16', NULL),
(59, 'Dizi Elemanlarını Yazdır (Ruby)', 'Bir dizinin elemanlarını ekrana yazdırır.', 'arr = [1, 2, 3, 4, 5]\narr.each { |x| puts x }', 13, 1, 1, 1, 1, '2025-07-01 11:07:16', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `snippet_tags`
--

CREATE TABLE `snippet_tags` (
  `snippet_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `snippet_views`
--

CREATE TABLE `snippet_views` (
  `id` int NOT NULL,
  `snippet_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `snippet_views`
--

INSERT INTO `snippet_views` (`id`, `snippet_id`, `user_id`, `ip_address`, `user_agent`, `viewed_at`) VALUES
(1, NULL, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 05:20:21'),
(2, NULL, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 05:20:31'),
(3, NULL, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 05:22:28'),
(4, NULL, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-02 05:22:37');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT '#6B7280',
  `usage_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `tags`
--

INSERT INTO `tags` (`id`, `name`, `color`, `usage_count`) VALUES
(1, 'React', '#61DAFB', 0),
(2, 'Vue.js', '#4FC08D', 0),
(3, 'Angular', '#DD0031', 0),
(4, 'Node.js', '#339933', 0),
(5, 'Express', '#000000', 0),
(6, 'Laravel', '#FF2D20', 0),
(7, 'Django', '#092E20', 0),
(8, 'Flask', '#000000', 0),
(9, 'jQuery', '#0769AD', 0),
(10, 'Bootstrap', '#7952B3', 0),
(11, 'Tailwind', '#06B6D4', 0),
(12, 'MongoDB', '#47A248', 0),
(13, 'MySQL', '#00000F', 0),
(14, 'PostgreSQL', '#336791', 0),
(15, 'Redis', '#DC382D', 0),
(16, 'Docker', '#2496ED', 0),
(17, 'Git', '#F05032', 0),
(18, 'AWS', '#FF9900', 0),
(19, 'Responsive', '#FF6B6B', 0),
(20, 'Animation', '#4ECDC4', 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `theme_preference` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `avatar`, `theme_preference`, `is_active`, `role`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$MX5WVW5IOQY9ypGRHC9l6ehRQh3asT49yq8USbvDBBBxhbAnMUL5G', 'Admin', 'Kullanıcı', NULL, NULL, 1, 'admin', '2025-07-01 11:07:03', NULL),
(2, 'test', 'test@example.com', '$2y$10$WlTPCOUsFrdFadNJlCci.eR09gapF2rOByF6P1WdnTRwDO8ZQ50x.', 'test', 'test', NULL, NULL, 1, 'user', '2025-07-01 13:30:52', NULL);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`snippet_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `snippets`
--
ALTER TABLE `snippets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `language_id` (`language_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `snippet_tags`
--
ALTER TABLE `snippet_tags`
  ADD PRIMARY KEY (`snippet_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Tablo için indeksler `snippet_views`
--
ALTER TABLE `snippet_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `snippet_id` (`snippet_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Tablo için AUTO_INCREMENT değeri `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Tablo için AUTO_INCREMENT değeri `snippets`
--
ALTER TABLE `snippets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Tablo için AUTO_INCREMENT değeri `snippet_views`
--
ALTER TABLE `snippet_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`snippet_id`) REFERENCES `snippets` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Tablo kısıtlamaları `snippets`
--
ALTER TABLE `snippets`
  ADD CONSTRAINT `snippets_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `snippets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `snippets_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `snippet_tags`
--
ALTER TABLE `snippet_tags`
  ADD CONSTRAINT `snippet_tags_ibfk_1` FOREIGN KEY (`snippet_id`) REFERENCES `snippets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `snippet_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `snippet_views`
--
ALTER TABLE `snippet_views`
  ADD CONSTRAINT `snippet_views_ibfk_1` FOREIGN KEY (`snippet_id`) REFERENCES `snippets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `snippet_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
