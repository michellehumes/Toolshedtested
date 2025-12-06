# Theme Deployment Guide

This guide explains how to deploy theme changes from your local repository to your Hostinger WordPress site using the automated SFTP deployment script.

## Overview

The deployment script (`deploy-theme.js`) automatically uploads your theme files to Hostinger via SFTP, eliminating the need for manual file uploads through the Hostinger File Manager or FTP clients.

## Prerequisites

- Node.js installed on your local machine (v14 or higher)
- Access to your Hostinger SFTP credentials
- Local clone of this repository

## One-Time Setup

### 1. Install Dependencies

Navigate to the theme directory and install required packages:

```bash
cd wp-content/themes/toolshed-tested
npm install ssh2-sftp-client dotenv
```

### 2. Configure SFTP Credentials

Create a `.env` file from the template:

```bash
cp .env.example .env
```

Edit `.env` with your Hostinger credentials:

```env
SFTP_HOST=your-site.hostinger.com
SFTP_PORT=22
SFTP_USERNAME=your_username
SFTP_PASSWORD=your_password
```

**Finding Your Hostinger SFTP Credentials:**

1. Log into Hostinger → hPanel
2. Go to **Files** → **FTP Accounts**
3. Use the credentials shown (or create a new FTP account)
4. The host is typically your domain or the server hostname shown in hPanel

### 3. Secure Your Credentials

Ensure `.env` is in your `.gitignore` file to prevent committing credentials:

```bash
# Check if .env is ignored
git check-ignore .env

# If not, add it to .gitignore
echo ".env" >> .gitignore
```

## Deploying Theme Changes

### Quick Deploy

From the theme directory, run:

```bash
node deploy-theme.js
```

The script will:
- Connect to your Hostinger server via SFTP
- Automatically create any missing directories
- Upload all configured theme files
- Show progress and report success/errors

### Files Deployed

The script currently deploys these files (configured in `deploy-theme.js`):

- `inc/class-tst-schema.php` - Schema markup functionality
- `inc/customizer.php` - Theme customizer options
- `footer.php` - Footer template
- `archive.php` - Category archive template
- `assets/css/components.css` - Component styles
- `template-parts/category/top-picks-table.php` - Top picks table component

### Adding More Files

To deploy additional files, edit the `filesToDeploy` array in `deploy-theme.js`:

```javascript
const filesToDeploy = [
  'inc/class-tst-schema.php',
  'inc/customizer.php',
  // Add your new files here:
  'header.php',
  'functions.php',
  // etc...
];
```

## Workflow Best Practices

### Recommended Development Flow

1. **Make changes locally** - Edit theme files in your local repository
2. **Test locally** - Use Local by Flywheel or another local WordPress environment
3. **Commit to Git** - Save your changes to version control
4. **Deploy to Hostinger** - Run `node deploy-theme.js`
5. **Test on live site** - Verify changes on your production site

### Multiple Developers

If multiple people are deploying:
- Each developer needs their own `.env` file (never shared)
- Use the same Hostinger SFTP credentials, or create separate FTP accounts
- Communicate before deploying to avoid overwriting each other's changes

## Troubleshooting

### Connection Errors

**Error: `ECONNREFUSED` or `ETIMEDOUT`**
- Check your SFTP host and port are correct
- Verify your Hostinger hosting is active
- Check if your IP is blocked (some hosts restrict SSH/SFTP access)

**Error: `Authentication failed`**
- Verify your SFTP username and password in `.env`
- Check that the FTP account is active in Hostinger hPanel
- Try resetting the FTP password in hPanel

### File Upload Errors

**Error: `Permission denied`**
- Ensure your FTP user has write permissions
- Check file ownership and permissions on the server

**Error: `No such file or directory`**
- The script will try to create missing directories
- Verify the remote path in `deploy-theme.js` matches your server structure

### Script Not Found

**Error: `Cannot find module 'ssh2-sftp-client'`**
- Run `npm install ssh2-sftp-client dotenv` in the theme directory
- Ensure you're running the command from `wp-content/themes/toolshed-tested/`

## Alternative Deployment Methods

### Via Hostinger File Manager (Manual)

If the script isn't working, you can manually upload files:

1. Log into Hostinger → hPanel
2. Go to **Files** → **File Manager**
3. Navigate to: `public_html/wp-content/themes/toolshed-tested/`
4. Upload changed files using the Upload button
5. Overwrite existing files when prompted

### Via FTP Client (FileZilla)

1. Download FileZilla (free FTP client)
2. Connect using your Hostinger SFTP credentials
3. Navigate to: `public_html/wp-content/themes/toolshed-tested/`
4. Drag and drop files from local to remote

## Security Notes

- **Never commit `.env` files** - They contain sensitive credentials
- **Use strong passwords** - For both FTP accounts and hosting
- **Limit FTP access** - Only give credentials to trusted developers
- **Consider SSH keys** - More secure than passwords (if Hostinger supports it)

## Support

- **Hostinger Support**: https://www.hostinger.com/contact
- **Script Issues**: Open an issue in this GitHub repository
- **WordPress Help**: https://wordpress.org/support/

## Advanced Configuration

### Changing Remote Path

If your WordPress installation is in a different location, edit `deploy-theme.js`:

```javascript
const REMOTE_BASE = '/public_html/wp-content/themes/toolshed-tested';
// Change to your path, e.g., '/wp-content/themes/toolshed-tested'
```

### Using SFTP Key Authentication

If your host supports SSH keys, you can use key-based authentication instead of passwords. Update `deploy-theme.js`:

```javascript
const config = {
  host: process.env.SFTP_HOST,
  port: process.env.SFTP_PORT || 22,
  username: process.env.SFTP_USERNAME,
  privateKey: fs.readFileSync('/path/to/your/private/key'),
};
```

## FAQ

**Q: Can I deploy the entire theme directory?**  
A: Yes, modify the `filesToDeploy` array to include all files, or write a loop to include all files recursively.

**Q: How long does deployment take?**  
A: Usually 5-15 seconds depending on file sizes and connection speed.

**Q: Can I schedule automatic deployments?**  
A: Not with this script alone. You'd need to set up a CI/CD pipeline (GitHub Actions, etc.).

**Q: Does this affect my existing WordPress script for pages?**  
A: No, this only uploads theme files. Your WordPress API script for pages is separate.

**Q: What if I accidentally delete a file on the server?**  
A: The script only uploads files, it won't delete. But you can manually re-upload all files by running the script.
