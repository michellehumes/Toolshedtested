#!/usr/bin/env node

/**
 * Theme Deployment Script for Hostinger
 * 
 * This script uploads the toolshed-tested theme files to Hostinger via SFTP
 * 
 * Setup:
 * 1. Copy .env.example to .env
 * 2. Fill in your Hostinger SFTP credentials
 * 3. Run: npm install ssh2-sftp-client dotenv
 * 4. Deploy: node deploy-theme.js
 */

const SftpClient = require('ssh2-sftp-client');
const path = require('path');
const fs = require('fs');
require('dotenv').config();

// Configuration
const config = {
  host: process.env.SFTP_HOST,
  port: process.env.SFTP_PORT || 22,
  username: process.env.SFTP_USERNAME,
  password: process.env.SFTP_PASSWORD,
};

// Theme files to deploy (relative to wp-content/themes/toolshed-tested/)
const filesToDeploy = [
  'inc/class-tst-schema.php',
  'inc/customizer.php',
  'footer.php',
  'archive.php',
  'assets/css/components.css',
  'template-parts/category/top-picks-table.php',
];

// Remote base path on Hostinger
const REMOTE_BASE = '/public_html/wp-content/themes/toolshed-tested';

// Local base path (current directory should be repo root)
const LOCAL_BASE = path.join(__dirname);

async function deployTheme() {
  const sftp = new SftpClient();
  
  console.log('🚀 Starting theme deployment to Hostinger...\n');
  
  // Validate configuration
  if (!config.host || !config.username || !config.password) {
    console.error('❌ Error: Missing SFTP credentials in .env file');
    console.error('Please copy .env.example to .env and fill in your credentials');
    process.exit(1);
  }

  try {
    // Connect to SFTP
    console.log(`📡 Connecting to ${config.host}...`);
    await sftp.connect(config);
    console.log('✅ Connected successfully!\n');

    // Deploy each file
    let successCount = 0;
    let errorCount = 0;

    for (const file of filesToDeploy) {
      const localPath = path.join(LOCAL_BASE, file);
      const remotePath = `${REMOTE_BASE}/${file}`;
      const remoteDir = path.dirname(remotePath);

      try {
        // Check if local file exists
        if (!fs.existsSync(localPath)) {
          console.log(`⚠️  Skipping ${file} (not found locally)`);
          continue;
        }

        // Ensure remote directory exists
        try {
          await sftp.mkdir(remoteDir, true);
        } catch (mkdirErr) {
          // Directory might already exist, continue
        }

        // Upload file
        console.log(`📤 Uploading ${file}...`);
        await sftp.put(localPath, remotePath);
        console.log(`✅ ${file} uploaded successfully`);
        successCount++;

      } catch (fileErr) {
        console.error(`❌ Error uploading ${file}: ${fileErr.message}`);
        errorCount++;
      }
    }

    console.log(`\n📊 Deployment Summary:`);
    console.log(`   ✅ Successful: ${successCount}`);
    console.log(`   ❌ Failed: ${errorCount}`);
    
    if (errorCount === 0) {
      console.log('\n🎉 Theme deployed successfully!');
    } else {
      console.log('\n⚠️  Deployment completed with errors');
    }

  } catch (err) {
    console.error('❌ Deployment failed:', err.message);
    process.exit(1);
  } finally {
    await sftp.end();
    console.log('\n🔌 Disconnected from server');
  }
}

// Run deployment
deployTheme().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});
