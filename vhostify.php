#!/usr/bin/php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration file path
$configFile = '/home/roygitonga/Documents/config.json';

// Function to load configuration
function loadConfig($configFile) {
    if (!file_exists($configFile) || !is_readable($configFile)) {
        die("Error: Unable to read configuration file.\n");
    }

    $config = json_decode(file_get_contents($configFile), true);

    if ($config === null) {
        die("Error: Invalid JSON format in configuration file.\n");
    }

    return $config;
}

// Load configuration
$config = loadConfig($configFile);

// Command-line interface setup
$options = getopt("v:h", ["version", "help"]);

if (isset($options['v']) || isset($options['version'])) {
    echo "vhostify version 1.0.0" . PHP_EOL;
    exit(0);
}

if (isset($options['h']) || isset($options['help'])) {
    echo <<<HELP
Usage: vhostify [OPTIONS] COMMAND [ARGS]...

Options:
  -v, --version  Show the version and exit.
  -h, --help     Show this message and exit.

Commands:
  config           Manage configuration variables
  explore          Browse current site path or site registered to DOMAIN
  forget (remove)  Unregister the current (or specified) PATH or DOMAIN
  link             Link current working directory to domain
  list             List all registered sites
  open             Open current site or site registered to DOMAIN in browser
  park (create)    Register the current (or specified) PATH to given DOMAIN
  rebuild          Rebuild all site configuration files
  refresh          Refresh configuration files of the site
  secure           Secure the site with a trusted TLS certificate
  set-root         Set document root for the site
  share            Generate public URL for the site
  show             Display site information (name, path, document root, secure)
  start            Restart all or specified services
  stop             Stop all or specified services

HELP;
    exit(0);
}

// Parse commands and arguments
$args = $argv;
$command = isset($args[1]) ? $args[1] : '';

// Command handlers
switch ($command) {
    case 'config':
        // Handle config command
        break;
    case 'explore':
        // Handle explore command
        break;
    case 'forget':
    case 'remove':
        // Handle forget/remove command
        break;
    case 'link':
        // Handle link command
        break;
    case 'list':
        // Handle list command
        break;
    case 'open':
        // Handle open command
        break;
    case 'park':
    case 'create':
        $domain = isset($args[2]) ? $args[2] : '';
        $path = isset($args[3]) ? $args[3] : '';
        createVirtualHost($domain, $path, $config);
        break;
    case 'rebuild':
        // Handle rebuild command
        break;
    case 'refresh':
        // Handle refresh command
        break;
    case 'secure':
        // Handle secure command
        break;
    case 'set-root':
        // Handle set-root command
        break;
    case 'share':
        // Handle share command
        break;
    case 'show':
        // Handle show command
        break;
    case 'start':
        // Handle start command
        break;
    case 'stop':
        // Handle stop command
        break;
    default:
        echo "Invalid command. Use 'vhostify --help' for usage information." . PHP_EOL;
        exit(1);
}

// Function to create a new virtual host
function createVirtualHost($domain, $path, $config) {
    if (!isset($config['paths']['conf']) || !isset($config['dns']['file']) || !isset($config['apache']['bin']) || !isset($config['apache']['conf'])) {
        die("Error: Missing configuration values.\n");
    }

    $confDir = $config['paths']['conf'];
    $hostsFile = $config['dns']['file'];
    $apacheBin = $config['apache']['bin'];
    $vhostsConfFile = $config['apache']['conf'];

    // Validate path and create directory if it doesn't exist
    if (!is_dir($path)) {
        if (!mkdir($path, 0755, true)) {
            echo "Error: Failed to create directory '$path'. Check permissions.\n";
            exit(1);
        }
        echo "Created directory: $path\n";
    }

    // Virtual host configuration content generation
    $vhostConfigContent = <<<CONF
<VirtualHost *:80>
    ServerName $domain
    DocumentRoot "$path"

    <Directory "$path">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/$domain.error.log
    CustomLog \${APACHE_LOG_DIR}/$domain.access.log combined
</VirtualHost>

CONF;

    // Write configuration to file
    $filename = $confDir . '/' . strtolower($domain) . '.conf';
    if (!file_put_contents($filename, $vhostConfigContent)) {
        echo "Error: Failed to write virtual host configuration file.\n";
        exit(1);
    }

    // Update /etc/hosts file
    $hostsEntry = "127.0.0.1\t$domain\n";
    if (file_put_contents($hostsFile, $hostsEntry, FILE_APPEND | LOCK_EX) === false) {
        echo "Error: Failed to update hosts file. Check permissions.\n";
        exit(1);
    }

    // Check Apache configuration and restart Apache
    $apacheConfigCheckCmd = "sudo $apacheBin -t";
    exec($apacheConfigCheckCmd, $output, $returnCode);
    if ($returnCode !== 0) {
        echo "Apache configuration check failed. Please fix configuration errors.\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }

    // Restart Apache
    echo "Restarting Apache...\n";
    $apacheRestartCmd = "sudo $apacheBin -k restart";
    exec($apacheRestartCmd, $output, $returnCode);
    if ($returnCode !== 0) {
        echo "Error: Apache restart failed. Check Apache configuration.\n";
        echo implode("\n", $output) . "\n";
        exit(1);
    }

    echo "Virtual host '$domain' created successfully.\n";
}
?>
