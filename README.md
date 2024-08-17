# DeployPHP

DeployPHP is a simple and efficient PHP tool for deploying your application to an FTP server by only uploading the differences between two Git hashes. This tool can be easily installed via Composer and configured with a`deploy.json` file.

## Features

- Deploy to an FTP server
- Only upload changes between two Git hashes
- Simple configuration

## Installation

You can install DeployPHP via Composer:

```bash
composer require iescarro/deployphp
```

## Configuration

Create a deploy.json file in the root of your project with the following structure:

```
{
  "host": "127.0.0.1",
  "username": "ftpuser",
  "password": "password",
  "default_remote_directory": "/",
  "target_hash": "HEAD",
  "source_hash": "fbd4937c5b1b6f79e88f0df4db138cd5f995e76a",
  "excluded_files": [
    ".gitignore",
    ".htaccess",
    "composer.json",
    "deploy.json",
    "README.md"
  ]
}
```

### Configuration Parameters

- host: The hostname of your FTP server.
- username: Your FTP username.
- password: Your FTP password.
- target_hash: The Git hash of the target commit.
- source_hash: The Git hash of the source commit.

## Usage

Once you have configured your deploy.json file, you can trigger the deployment using the following command:

```
php vendor/iescarro/deployphp/deploy
```

This command will compare the differences between the specified Git hashes and upload the changed files to the FTP server.

## Contributing

We welcome contributions to improve DeployPHP. Please fork the repository and create a pull request with your changes.

## License

DeployPHP is open-sourced software licensed under the MIT license.

## Contact

For any questions or issues, please open an issue on GitHub or contact the maintainer.