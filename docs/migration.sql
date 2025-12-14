-- SQL Migration for NewsAiSuggestion table
-- Generated for Holiday and Time AI Knowledge Base Integration
-- Date: 2025-12-02

-- Create the news_ai_suggestion table
CREATE TABLE IF NOT EXISTS news_ai_suggestion (
    id INT AUTO_INCREMENT NOT NULL,
    news_id INT NOT NULL,
    extracted_instructions LONGTEXT NOT NULL,
    suggested_title LONGTEXT DEFAULT NULL,
    suggested_content LONGTEXT DEFAULT NULL,
    suggestion_type VARCHAR(50) NOT NULL,
    matched_knowledgebase_id INT DEFAULT NULL,
    matched_knowledgebase_name LONGTEXT DEFAULT NULL,
    match_confidence DOUBLE PRECISION DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    category_name LONGTEXT DEFAULT NULL,
    template_used LONGTEXT DEFAULT NULL,
    ai_metadata JSON DEFAULT NULL,
    applied_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    creation_date DATETIME NOT NULL,
    modification_date DATETIME NOT NULL,
    created_by VARCHAR(255) DEFAULT NULL,
    modified_by VARCHAR(255) DEFAULT NULL,
    INDEX IDX_NEWS_AI_SUGGESTION_NEWS (news_id),
    INDEX IDX_NEWS_AI_SUGGESTION_STATUS (status),
    INDEX IDX_NEWS_AI_SUGGESTION_TYPE (suggestion_type),
    INDEX IDX_NEWS_AI_SUGGESTION_CREATED (creation_date),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Add foreign key constraint to news table
ALTER TABLE news_ai_suggestion 
    ADD CONSTRAINT FK_NEWS_AI_SUGGESTION_NEWS 
    FOREIGN KEY (news_id) 
    REFERENCES news (id) 
    ON DELETE CASCADE;

-- Add comment to table
ALTER TABLE news_ai_suggestion COMMENT = 'Stores AI-generated suggestions for knowledge base updates from news articles';

-- Useful queries for checking the migration

-- Check if table exists
SELECT COUNT(*) as table_exists 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'news_ai_suggestion';

-- Check table structure
DESCRIBE news_ai_suggestion;

-- Check indexes
SHOW INDEX FROM news_ai_suggestion;

-- Check foreign keys
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'news_ai_suggestion'
AND REFERENCED_TABLE_NAME IS NOT NULL;
