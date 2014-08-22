# PR stats

This small tool shows a small statistic over configured repositories.
When the url http://localhost:8000/repo/{repository} gets opened the github will be queried to get data about all open PRs.
The data will be stored in the configured `prLog/{repository}` directory and displayed in the stats.

#### [Why, How and What: Read here!](https://github.com/dazz/repo-pr-stats/blob/master/doc/why-what-how.md)

### Run

* clone
* `php -S localhost:8000 -t web/`
* open http://localhost:8000/ in browser

### Config Requirements:

* Copy `config/config.dist.php` to `config/config.php`
* Add github token
* Add repositories to get stats for

### Screenshot

![first screenshot](https://cloud.githubusercontent.com/assets/182954/4017368/576fd744-2a3f-11e4-9200-29745af1bf13.png)