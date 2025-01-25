# Github-Activity CLI
Sample solution for [github-activity](url) challenge from [roadmap.sh](roadmap.sh)

<br>

## How to run
Clone the repository:

```bash
git clone https://github.com/connecttoMAHDI/github-activity-cli.git
```
Then navigate to the project's directory:
```
cd github-activity-cli
```

Run the following command to see a how it works:
```bash
php github-activity.php {your github username}
``` 

<br>

### Having Issues?  
If you encounter the following error while executing the command:  
```bash
Failed to fetch events from Github: SSL certificate problem: unable to get local issuer certificate
```  
Follow these steps to resolve the issue:  

1. **Download the CA Certificates**  
   - Download the latest `cacert.pem` file (a bundle of trusted CA certificates) from [here](https://curl.se/ca/cacert.pem).  

2. **Save the File**  
   - Save the downloaded `cacert.pem` file in:  
     ```bash
     {path\to}\php\extras\ssl\cacert.pem
     ```  

3. **Update PHP Configuration**  
   - Run the following command to locate your `php.ini` file:  
     ```bash
     php --ini
     ```  
   - Open the `php.ini` file in a text editor.  

4. **Enable Required Extensions**  
   - Search for the terms `openssl` and `curl` in the file and ensure they are **not commented** (remove the `;` at the beginning of the line if present).  

5. **Set the `curl.cainfo` Path**  
   - Search for `curl.cainfo` in the `php.ini` file.  
   - Assign the full path to the `cacert.pem` file:  
     ```ini
     curl.cainfo = "{path\to}\php\extras\ssl\cacert.pem"
     ```  
