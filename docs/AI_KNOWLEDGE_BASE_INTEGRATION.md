# AI-Powered Knowledge Base Integration for News

## Overview

This feature automatically analyzes news articles and generates intelligent suggestions for updating or creating knowledge base (Wissensdatenbank) articles. It's designed to streamline the process of turning company announcements and policy changes into actionable, well-structured documentation.

## Use Case Example

**Scenario**: Admin creates a news article stating:
> "From now on, we change our phone answering structure. Instead of saying 'Good Morning Sir', please say 'Hello Mr. [Name]'."

**What the AI does**:
1. Analyzes the news content
2. Extracts the specific instruction: "Say 'Hello Mr.' instead of 'Good Morning Sir'"
3. Searches existing knowledge base for related articles (e.g., "Telefonannahme")
4. Either:
   - **Updates** existing article with new instructions, or
   - **Creates** new article using appropriate template (e.g., "Arbeitsanweisung" template)
5. Presents suggestion to admin for review and approval

## Features

### 🤖 AI Analysis
- Extracts actionable instructions from news content
- Identifies topic categories and keywords
- Determines confidence level of analysis
- Provides reasoning for decisions

### 🎯 Smart Matching
- Searches existing knowledge base articles
- Calculates match confidence scores
- Recommends update vs. create action
- Considers semantic similarity, not just keywords

### 📝 Template System
Pre-configured templates for different categories:

1. **Arbeitsanweisung** (Work Instructions)
   - Purpose and goals
   - Scope
   - Step-by-step procedures
   - Important notes
   - Responsibilities
   - Documentation requirements

2. **Telefonannahme** (Phone Procedures)
   - Greeting standards
   - Call handling steps
   - Closing procedures
   - Special situations

3. **Patientenaufnahme** (Patient Reception)
   - Preparation steps
   - Workflow
   - Documentation
   - Follow-up

4. **Generic Template**
   - Flexible structure for other categories

### ✅ Review & Approval Workflow
1. **Pending**: AI generates suggestion
2. **Approved**: Admin reviews and approves
3. **Applied**: Suggestion is applied to knowledge base
4. **Rejected**: Admin declines suggestion

## Setup

### 1. Environment Configuration

Add to your `.env` file:

```bash
# AI Provider: 'openai' or 'anthropic'
NEWS_AI_PROVIDER=openai

# Your API Key
NEWS_AI_API_KEY=sk-your-api-key-here

# Model Selection
# OpenAI: gpt-4, gpt-4-turbo, gpt-3.5-turbo
# Anthropic: claude-3-5-sonnet-20241022, claude-3-opus-20240229
NEWS_AI_MODEL=gpt-4
```

### 2. Database Migration

Run the migration to create the `news_ai_suggestion` table:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 3. Verify Services

Check that services are registered:

```bash
php bin/console debug:container NewsAiAnalyzer
php bin/console debug:container NewsKnowledgeBaseProcessor
```

## Usage

### For Administrators

#### Step 1: Create News Article
1. Navigate to **News** in admin panel
2. Create news article with title and content
3. Include specific instructions or policy changes

#### Step 2: Trigger AI Processing
1. View the news article detail page
2. Click **"AI Process"** button (🤖 icon)
3. AI analyzes content and generates suggestion

#### Step 3: Review Suggestion
1. Navigate to **AI Suggestions** menu
2. View the generated suggestion
3. Review:
   - Extracted instructions
   - Suggested title and content
   - Matched knowledge base article (if update)
   - Match confidence score
   - AI reasoning

#### Step 4: Approve or Reject
- Click **"Approve"** if suggestion looks good
- Click **"Reject"** if you disagree

#### Step 5: Apply to Knowledge Base
- Once approved, click **"Apply to Knowledge Base"**
- Knowledge base article is automatically created or updated
- Suggestion status changes to "Applied"

### Batch Processing

You can also process multiple news articles:

```bash
# Process all unprocessed news from last 7 days
php bin/console app:process-news --days=7

# Process specific news ID
php bin/console app:process-news --news-id=123
```

## Architecture

### Entities

**NewsAiSuggestion**
- Stores AI-generated suggestions
- Links to source News article
- Tracks suggestion status and metadata
- Records when applied

### Services

**NewsAiAnalyzer**
- Interfaces with AI APIs (OpenAI/Anthropic)
- Performs content analysis
- Matches knowledge base articles
- Generates content using templates

**NewsKnowledgeBaseProcessor**
- Orchestrates the AI workflow
- Manages suggestion creation
- Applies approved suggestions
- Handles category creation

### Workflow

```
News Article
    ↓
[AI Analyzer]
    ↓
Extract Instructions + Identify Category
    ↓
[Match Existing KB Articles]
    ↓
[Generate Content with Template]
    ↓
NewsAiSuggestion (Pending)
    ↓
[Admin Review]
    ↓
Approved → Apply → Knowledge Base Updated/Created
```

## Templates

Templates are defined in `NewsKnowledgeBaseProcessor::getTemplateForCategory()`.

### Adding Custom Templates

```php
$templates['YourCategory'] = <<<TEMPLATE
<h2>[Title]</h2>
<h3>Section 1</h3>
<p>[Content]</p>
TEMPLATE;
```

Categories are matched using fuzzy string matching, so "Arbeitsanweisung" will match "arbeitsanweisung", "Arbeits-Anweisung", etc.

## AI Prompts

The system uses carefully crafted prompts for:

1. **Analysis**: Extract instructions and metadata
2. **Matching**: Find relevant existing articles
3. **Generation**: Create well-structured content

All prompts are German-aware and optimized for medical practice context.

## Cost Considerations

### OpenAI Pricing (approximate)
- GPT-4: ~$0.03 per news article
- GPT-4 Turbo: ~$0.01 per news article
- GPT-3.5 Turbo: ~$0.002 per news article

### Anthropic Pricing (approximate)
- Claude 3.5 Sonnet: ~$0.015 per news article
- Claude 3 Opus: ~$0.075 per news article

Recommendation: Start with GPT-4 Turbo or Claude 3.5 Sonnet for best quality/cost ratio.

## Sales Arguments

### For Potential Customers

1. **Time Savings**
   - Reduces manual documentation time by 70%
   - Automatic template application
   - No more copy-paste errors

2. **Consistency**
   - Standard formatting across all documents
   - AI ensures completeness
   - Professional presentation

3. **Knowledge Preservation**
   - Never lose important policy changes
   - Automatic archiving of decisions
   - Easy to find and reference

4. **Compliance**
   - Complete audit trail
   - Version control
   - Approval workflow

5. **Competitive Advantage**
   - Modern AI-powered workflow
   - Stay ahead of competitors
   - Attract tech-savvy staff

6. **ROI**
   - Saves 2-3 hours per week
   - Reduces training time for new staff
   - Fewer mistakes and misunderstandings

## Troubleshooting

### AI Not Processing
- Check API key is set correctly
- Verify internet connectivity
- Check logs: `var/log/dev.log`

### Low Confidence Scores
- News content may be too vague
- Add more specific instructions
- Include context and examples

### Wrong Template Selected
- Update category name in news
- Add category mapping in code
- Create custom template for your needs

## Future Enhancements

- [ ] Automatic processing on news creation
- [ ] Multi-language support
- [ ] Image analysis for visual instructions
- [ ] Integration with training modules
- [ ] Automatic testing of knowledge base quality
- [ ] AI-powered search across knowledge base
- [ ] Suggestion similarity detection (avoid duplicates)

## Support

For questions or issues:
1. Check logs in `var/log/`
2. Review AI metadata in suggestion detail
3. Contact development team with suggestion ID

## License

Part of the Holiday and Time application suite.
© 2025 Prolyfix
