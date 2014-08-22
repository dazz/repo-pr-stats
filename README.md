# PR stats

This small tool shows a small statistic over configured repositories.
When the url http://localhost:8002/repo/{repository} gets opened the github will be queried to get data about all open PRs.
The data will be stored in the configured `prLog/{repository}` directory and displayed in the stats.

### Run

* clone me
* run `composer start` or manually run `php -S localhost:8002 -t web/`
* open <http://localhost:8002> in browser

### Config Requirements:

* Copy `config/config.dist.php` to `config/config.php`
* Add github token
* Add repositories to get stats for

### Screenshot

![first screenshot](https://cloud.githubusercontent.com/assets/182954/3973471/3d1b5048-27e8-11e4-8aba-d83044737073.png)
