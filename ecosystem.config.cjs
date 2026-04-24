module.exports = {
  apps: [
    {
      name: 'lpd-php',
      script: '/usr/bin/php',
      args: '-S 0.0.0.0:8080 /home/user/lpd_php/router.php',
      cwd: '/home/user/lpd_php',
      interpreter: 'none',
      watch: false,
      instances: 1,
      exec_mode: 'fork',
      env: {
        DB_CONNECTION: 'sqlite',
        SQLITE_PATH: '/home/user/lpd_php/data/lpd_canggu.sqlite',
      },
      error_file: '/home/user/lpd_php/logs/pm2-error.log',
      out_file:   '/home/user/lpd_php/logs/pm2-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss',
    }
  ]
};
