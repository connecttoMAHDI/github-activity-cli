# GitHub-Activity CLI

A lightweight CLI tool that fetches and displays a GitHub user's public activity in a structured and user-friendly format. This is a sample solution to the [GitHub User Activity](https://roadmap.sh/projects/github-user-activity) challenge from [roadmap.sh](https://roadmap.sh).

---

## Features
- Fetches public activities of any GitHub user.
- Caches data for **15 minutes** using Redis and the Predis package for faster subsequent fetches.
- Displays activity details like repository creation, commits, pull requests, and more.
- Handles common errors gracefully (e.g., username not found, rate limits, service outages).
- Simple and easy-to-use CLI interface.

---

## Installation & Setup

### Step 1: Clone the Repository
First, clone the project to your local machine:
```bash
git clone https://github.com/connecttoMAHDI/github-activity-cli.git
```

### Step 2: Navigate to the Project
Change into the project directory:
```bash
cd github-activity-cli
```

### Step 3: Start Redis Server
The CLI uses Redis caching to improve performance. You must start your Redis server before running the script. If you're unfamiliar with Redis, watch this [YouTube tutorial](https://youtu.be/ioCaSHNhIJA?si=okD7eE_Rm92z2Zxd) to get started.

### Step 4: Rename `.env.example` to `.env`
Before running the script, rename the `.env.example` file to `.env` to configure the required environment variables correctly.

```bash
mv .env.example .env  # Linux/macOS
ren .env.example .env  # Windows
```

### Step 5: Run the CLI
Use the following command to fetch and display the activity of a GitHub user:
```bash
php github-activity.php {GitHub username}
```

---

## Example Output
Here’s an example of how the CLI output might look:
```
- Created a branch named master in connecttoMAHDI/weather-api
- Pushed 3 commits to connecttoMAHDI/number-guessing-game
- Starred goodnesskay/Laravel-Open-Source-Projects
- Forked connecttoMAHDI/sample-repo to connecttoMAHDI/my-forked-repo
- Opened pull request #42 in connecttoMAHDI/github-activity-cli
```

---

## Troubleshooting

### Common Issue: Redis Connection Error
If you see an error like:
```
Connection to Redis refused!
Ensure the Redis server is running and credentials are correct.
```
Make sure Redis is running before executing the script. If Redis is not installed, follow [this tutorial](https://youtu.be/ioCaSHNhIJA?si=okD7eE_Rm92z2Zxd) to install and configure it properly.

### Common Issue: SSL Certificate Error
If you encounter an error like this:
```bash
Failed to fetch events: SSL certificate problem: unable to get local issuer certificate
```

Follow these steps to resolve it:

1. **Download the CA Certificates**
   - Download the latest `cacert.pem` file from [this link](https://curl.se/ca/cacert.pem).

2. **Save the File**
   - Save the downloaded `cacert.pem` file to:
     ```
     {path\to}\php\extras\ssl\cacert.pem
     ```

3. **Update PHP Configuration**
   - Locate your `php.ini` file using:
     ```bash
     php --ini
     ```
   - Open the `php.ini` file in a text editor.

4. **Enable Required Extensions**
   - Ensure `openssl` and `curl` extensions are enabled (remove the `;` before the lines if present).

5. **Set the `curl.cainfo` Path**
   - Search for `curl.cainfo` in the `php.ini` file and set it to the full path of your `cacert.pem` file:
     ```ini
     curl.cainfo = "{path\to}\php\extras\ssl\cacert.pem"
     ```

6. **Restart Services**
   - Restart your web server or PHP service for the changes to take effect.

