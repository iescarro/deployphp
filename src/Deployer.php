<?php

namespace DeployPHP;

class Deployer
{
  private function get_diff_files($hash1, $hash2)
  {
    $command = "git diff --name-only $hash1 $hash2";
    $output = [];
    $returnVar = 0;
    exec($command, $files, $returnVar);

    if ($returnVar !== 0) {
      echo "Error running git diff. Make sure you are in a Git repository and the hashes are correct.\n";
      exit(1);
    }
    return $files;
  }

  function run($config_file)
  {
    $red = "\033[31m";
    $green = "\033[32m";
    $yellow = "\033[33m";
    $reset = "\033[0m";

    $config = $this->get_config($config_file);

    $ftp = new Ftp($config['host']);
    $ftp->login($config['username'], $config['password']);

    $files = $this->get_diff_files($config['source_hash'], $config['target_hash']);

    echo count($files) . " files changed.\n";
    $i = 1;
    foreach ($files as $file) {
      if (!in_array($file, $config['excluded_files'])) {
        $ftp->upload($file, '.', function ($e) use ($i) {
          echo $i . " " . $e;
        }, function ($e) use ($green, $reset) {
          echo " [{$green}$e{$reset}]\n";
        }, function ($e) use ($red, $reset) {
          echo " [{$red}$e{$reset}]\n";
        });
      } else {
        echo "$i Ignoring $file. [{$yellow}Ignored{$reset}]\n";
      }
      $i++;
    }
    echo "Done\n\n";

    $this->update_config($config, $config_file);
  }

  private function update_config($config, $config_file)
  {
    $hash = trim(shell_exec('git rev-parse HEAD'));

    if ($hash === '') {
      die("Failed to get the current Git hash. Ensure that this script is running in a Git repository.\n");
    }

    $config['source_hash'] = $hash;
    $json_data = json_encode($config, JSON_PRETTY_PRINT);
    file_put_contents($config_file, $json_data);
  }

  private function get_config($config_file)
  {
    $data = file_get_contents($config_file);
    $config = json_decode($data, true);

    return $config;
  }
}

class Ftp
{
  var $conn;
  var $username;
  var $password;

  function __construct($host)
  {
    $this->conn = ftp_connect($host);
  }

  function login($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
    $result = ftp_login($this->conn, $username, $password);
    ftp_pasv($this->conn, true);
  }

  function upload($filename, $root_dir, $uploading, $success, $error)
  {
    $local_file = $filename;
    $base_file = basename($filename);
    $dir = dirname($filename);

    $this->create_directory($this->conn, $root_dir . '/' . $dir);

    $remote_dir = trim($root_dir . '/' . $dir, '.');
    $remote_file = $remote_dir . '/' . $base_file;
    call_user_func($uploading, "Uploading $base_file to $remote_dir");
    $result = ftp_put($this->conn, $remote_file, $local_file, FTP_BINARY);
    if ($result) {
      call_user_func($success, "OK");
    } else {
      call_user_func($error, "Not OK");
    }
  }

  function create_directory($conn, $dir)
  {
    $parts = explode('/', $dir);
    $currentDir = '';
    foreach ($parts as $part) {
      if ($part == '') {
        continue; // Skip empty parts
      }
      $currentDir .= '/' . $part;
      if (!@ftp_chdir($conn, $currentDir)) {
        if (!ftp_mkdir($conn, $currentDir)) {
          echo "Failed to create directory: $currentDir\n";
          return false;
        }
      }
    }
    return true;
  }
}
