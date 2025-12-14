# Quick Start Guide: AI Knowledge Base Integration

## 🚀 Get Started in 5 Minutes

### Step 1: Get Your AI API Key

#### Option A: OpenAI (Recommended for beginners)
1. Go to https://platform.openai.com/api-keys
2. Create account or sign in
3. Click "Create new secret key"
4. Copy the key (starts with `sk-...`)

#### Option B: Anthropic (Better for German language)
1. Go to https://console.anthropic.com/
2. Create account or sign in
3. Navigate to API Keys
4. Create new key
5. Copy the key

### Step 2: Configure Environment

Edit `.env` file in your project root:

```bash
# Add these lines:
NEWS_AI_PROVIDER=openai
NEWS_AI_API_KEY=sk-your-actual-key-here
NEWS_AI_MODEL=gpt-4-turbo
```

For Anthropic:
```bash
NEWS_AI_PROVIDER=anthropic
NEWS_AI_API_KEY=your-anthropic-key
NEWS_AI_MODEL=claude-3-5-sonnet-20241022
```

### Step 3: Create Database Table

```bash
php bin/console doctrine:schema:update --force
```

Or generate migration:
```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Step 4: Test the Setup

```bash
# Verify services are loaded
php bin/console debug:container NewsAiAnalyzer

# Should show: Prolyfix\RssBundle\Service\NewsAiAnalyzer
```

### Step 5: Try It Out!

1. Go to admin panel → **News**
2. Create a test news article:
   ```
   Title: Phone Procedure Update
   Content: From now on, when answering the phone, please say 
   "Hello, [Practice Name], [Your Name] speaking" instead of 
   the old greeting. This creates a more professional impression.
   ```
3. Click **"AI Process"** button 🤖
4. Navigate to **AI Suggestions**
5. Review the generated suggestion
6. Click **"Approve"** then **"Apply to Knowledge Base"**
7. Check **Knowledge Base** → new article created! ✅

## 📊 Expected Results

**Analysis Result:**
- ✅ Instructions extracted: "Say 'Hello, [Practice Name]...' instead of old greeting"
- ✅ Category identified: "Telefonannahme" or "Phone Procedures"
- ✅ Confidence: 0.85-0.95
- ✅ Action: Create new article (or update if similar exists)

**Generated Content:**
- Professional title
- Structured using "Telefonannahme" template
- Clear sections: Begrüßung, Gesprächsführung, etc.
- Highlights the specific instruction
- Ready to use immediately

## 🎯 Best Practices

### Writing News for Better AI Results

**Good Example:**
```
Title: New Patient Check-in Procedure
Content: Starting Monday, all patients must:
1. Check in at the new digital kiosk
2. Update their insurance card
3. Confirm their appointment time

Please ensure patients know about this change.
```

**Why it works:**
- Clear, specific instructions
- Action-oriented language
- Numbered steps
- Context provided

**Bad Example:**
```
Title: Some Changes
Content: We're doing things differently now.
```

**Why it doesn't work:**
- Too vague
- No specific actions
- No context

### Tips for Maximum Value

1. **Be Specific**: "Say X instead of Y" not "Improve phone manners"
2. **Include Context**: Why the change matters
3. **Use Action Verbs**: "Please do X" not "We might want to X"
4. **Add Examples**: Show correct vs incorrect
5. **Mention Category**: "For phone procedures..." helps AI categorize

## 🔧 Troubleshooting

### "No actionable instructions found"

**Cause**: Content is too general or informational

**Fix**: Rewrite news with specific actions:
- Before: "We care about good service"
- After: "When greeting customers, always stand up and smile"

### "API Key Invalid"

**Cause**: Wrong key or missing from `.env`

**Fix**:
1. Check `.env` file has correct key
2. Clear cache: `php bin/console cache:clear`
3. Verify key hasn't expired

### "Low confidence score (<0.5)"

**Cause**: AI unsure about interpretation

**Fix**: Add more detail and context to news

## 💰 Cost Estimate

**Typical News Article Processing:**
- Analysis: ~1,000 tokens
- Matching: ~1,500 tokens
- Generation: ~2,000 tokens
- **Total: ~4,500 tokens**

**Monthly Cost (processing 50 news/month):**
- OpenAI GPT-4 Turbo: ~€2-3
- Anthropic Claude 3.5: ~€3-4
- OpenAI GPT-3.5: ~€0.20

**ROI Calculation:**
- Time saved per news: 15-20 minutes
- 50 news/month × 15 min = 12.5 hours
- At €30/hour = €375 saved
- **Cost: €3 → Save: €372/month** 💰

## 📈 Scaling Up

### Processing Multiple News at Once

```bash
# Process all news from last week
php bin/console app:process-news-ai --days=7

# Process up to 50 articles
php bin/console app:process-news-ai --days=30 --limit=50

# Reprocess everything (even with existing suggestions)
php bin/console app:process-news-ai --days=90 --force
```

### Automation

Add to cron (runs daily at 2 AM):
```bash
0 2 * * * cd /path/to/project && php bin/console app:process-news-ai --days=1
```

### Bulk Approval Workflow

1. Process 10-20 news articles
2. Review all suggestions in one session
3. Approve good ones
4. Apply all approved in batch
5. Review resulting knowledge base articles

## 🎓 Training Your Team

### For Content Creators

"When writing news about procedures or policy changes:
1. Be specific about what should change
2. Include the old way and new way
3. Explain why it matters
4. The AI will automatically create documentation"

### For Reviewers

"AI suggestions need your expertise:
1. Check if instructions are complete
2. Verify category is correct
3. Ensure content is practice-appropriate
4. Approve if 80% good (you can edit after)
5. Reject if fundamentally wrong"

## 🎁 Sales Demo Script

**For Prospects:**

> "Let me show you something amazing. You write a news article 
> about a new phone procedure. Click one button. The AI reads it, 
> understands what changed, finds the right documentation to update, 
> and creates a perfectly formatted knowledge base article using 
> your templates. What took 30 minutes now takes 30 seconds. 
> And it never forgets to document something."

**Demo Steps:**
1. Show news creation (1 min)
2. Click AI Process button (dramatic pause)
3. Show generated suggestion (wow moment)
4. Apply to knowledge base (instant result)
5. Show final polished article (professional)

**Key Messages:**
- ⚡ **Speed**: 30 seconds vs 30 minutes
- 🎯 **Accuracy**: Never forgets details
- 📚 **Consistency**: Always uses templates
- 💰 **ROI**: Pays for itself in week 1

## 🆘 Support

**Common Issues:**

| Problem | Solution |
|---------|----------|
| AI not processing | Check API key in .env |
| Wrong category | Update news with category hints |
| Duplicate suggestions | Use `--force` flag carefully |
| Slow processing | Normal for first request (API warmup) |

**Need Help?**

1. Check logs: `tail -f var/log/dev.log`
2. Test API: `php bin/console debug:container NewsAiAnalyzer`
3. Review suggestion metadata (click suggestion detail)
4. Contact support with suggestion ID

## ✨ Next Steps

Now that you're set up:

1. ✅ Process 5-10 test news articles
2. ✅ Review and approve suggestions
3. ✅ Train your team on best practices
4. ✅ Set up automated daily processing
5. ✅ Monitor quality and adjust prompts
6. ✅ Expand to more categories
7. ✅ Measure time savings

**Welcome to the future of knowledge management! 🚀**
