-- YouTube Processor Database Schema
-- Compatible with MySQL/MariaDB

-- Create database
CREATE DATABASE IF NOT EXISTS youtube_processor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE youtube_processor;

-- YouTube Channels Table
CREATE TABLE youtube_channels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  channel_id VARCHAR(255) NOT NULL UNIQUE,
  channel_name VARCHAR(255) NOT NULL,
  avatar_url TEXT,
  banner_url TEXT,
  subscriber_count INT,
  video_count INT,
  processed_count INT DEFAULT 0,
  last_scan DATETIME,
  next_scan DATETIME,
  scan_frequency VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Scan Logs Table
CREATE TABLE scan_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  channel_id INT,
  start_time DATETIME,
  end_time DATETIME,
  status VARCHAR(50),
  videos_found INT,
  videos_added INT,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (channel_id) REFERENCES youtube_channels(id) ON DELETE CASCADE
);

-- Videos Table
CREATE TABLE videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  youtube_id VARCHAR(255) NOT NULL UNIQUE,
  channel_id INT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  thumbnail_url TEXT,
  publish_date DATETIME,
  duration INT,
  status VARCHAR(50) DEFAULT 'pending',
  processing_started DATETIME,
  processing_completed DATETIME,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (channel_id) REFERENCES youtube_channels(id) ON DELETE SET NULL
);

-- Video Processing Table
CREATE TABLE video_processing (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT,
  local_video_path VARCHAR(255),
  audio_path VARCHAR(255),
  subtitle_path VARCHAR(255),
  processing_stage VARCHAR(50),
  processing_status VARCHAR(50),
  processing_settings TEXT,
  started_at DATETIME,
  completed_at DATETIME,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Content Analysis Table
CREATE TABLE content_analysis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT,
  original_content TEXT,
  structured_content TEXT,
  analysis_status VARCHAR(50),
  api_used VARCHAR(50),
  tokens_used INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Rewritten Content Table
CREATE TABLE rewritten_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT,
  rewrite_level VARCHAR(50),
  change_names BOOLEAN DEFAULT 0,
  change_locations BOOLEAN DEFAULT 0,
  change_examples BOOLEAN DEFAULT 0,
  add_details BOOLEAN DEFAULT 0,
  hook TEXT,
  introduction TEXT,
  main_content TEXT,
  climax TEXT,
  twist TEXT,
  transition TEXT,
  controversy TEXT,
  conclusion TEXT,
  call_to_action TEXT,
  api_used VARCHAR(50),
  tokens_used INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Generated Images Table
CREATE TABLE generated_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content_section_id INT,
  content_section_type VARCHAR(50),
  image_prompt TEXT,
  image_path VARCHAR(255),
  api_used VARCHAR(50),
  style VARCHAR(50),
  generation_status VARCHAR(50),
  width INT,
  height INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exported Projects Table
CREATE TABLE exported_projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  video_id INT,
  export_format VARCHAR(50),
  include_subtitles BOOLEAN DEFAULT 0,
  include_images BOOLEAN DEFAULT 0,
  include_prompts BOOLEAN DEFAULT 0,
  export_path VARCHAR(255),
  exported_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'user',
  api_limit INT,
  api_used INT DEFAULT 0,
  subscription_type VARCHAR(50),
  subscription_expires DATETIME,
  remember_token VARCHAR(255),
  last_login DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- System Settings Table
CREATE TABLE system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(255) NOT NULL UNIQUE,
  setting_value TEXT,
  setting_group VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create default admin user (password: admin123)
INSERT INTO users (username, email, password, role, api_limit, created_at, updated_at)
VALUES ('admin', 'admin@example.com', '$2y$10$tG.B/xFM.bAQzUTeZ0KLBeWGHJGcVhSPDLQnRsTyfFBqkXvgskUZi', 'admin', 0, NOW(), NOW());

-- Create default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_group)
VALUES 
-- API Settings
('api.youtube.api_key', '', 'api'),
('api.youtube.client_id', '', 'api'),
('api.youtube.client_secret', '', 'api'),
('api.speech_to_text.assembly_ai.api_key', '', 'api'),
('api.speech_to_text.rev_ai.api_key', '', 'api'),
('api.speech_to_text.whisper.api_key', '', 'api'),
('api.ai_content.openai.api_key', '', 'api'),
('api.ai_content.claude.api_key', '', 'api'),
('api.image_generation.dall_e.api_key', '', 'api'),
('api.image_generation.midjourney.api_key', '', 'api'),
('api.image_generation.stable_diffusion.api_key', '', 'api'),

-- Processing Settings
('processing.max_video_duration', '3600', 'processing'),
('processing.max_video_size', '524288000', 'processing'),
('processing.allowed_video_formats', 'mp4,webm,mkv', 'processing'),
('processing.default_language', 'vi', 'processing'),
('processing.default_tone', 'informative', 'processing'),

-- Scan Settings
('scan.max_videos_per_scan', '10', 'scan'),
('scan.frequency_options.hourly', '3600', 'scan'),
('scan.frequency_options.6_hours', '21600', 'scan'),
('scan.frequency_options.12_hours', '43200', 'scan'),
('scan.frequency_options.daily', '86400', 'scan'),
('scan.frequency_options.weekly', '604800', 'scan'),

-- Image Settings
('image.default_style', 'realistic', 'image'),
('image.default_width', '1024', 'image'),
('image.default_height', '1024', 'image'),
('image.default_prompt_template', 'Professional cinematic style photo, ultra-detailed, 8k, dramatic lighting, [theme] visual storytelling, high-quality, high-definition', 'image'),

-- User Settings
('users.default_api_limit', '100', 'users'),
('users.default_role', 'user', 'users');
