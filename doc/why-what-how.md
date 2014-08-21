## Why is this tool here?

TLDR; To help you to ensure quality of your project.

### Age of Pull Requests
Pull requests should be small. Small in number of changed lines. It is easier to review, approve and integrate a patch that has a small diff,
than a big one that takes up a few days to even understand what every change means and how it can get tested.

When the master changes fast and pull requests are there for more than a few hours then the effort to keep it mergeable is high.

In an actively developed project an indication that a Pull Request is too big is the number of days it has been open.

### Assigning a Pull Request
A Pull Request should be assigned to the one who is responsible for working on it so it is easy to know where to direct questions about the path a pull request takes.

### Adding a description to the Pull Request
Giving the reviewer all the information he needs to know what this PR is about, how to test it and why it was made helps her to give feedback and/or integrate the feature faster.
If it is hard to find out then the reviewer might not be as motivated to read the code to follow the thought process of the creator.

Here are a few things to make the reviewers life nice and easy:

* Include a short and concise summary of what this PR addresses so the reviewer can get an idea what to expect.
* Add references: Was there a discussion before that can be referenced or documentation or a bug-report?
* If necessary, include setup steps.
* Include a comment how this is should be tested. Expect the reviewer has never opened the app and really does not know anything about it. What does he need to know in order to see that the bug has been fixed or that the feature is working as expected.
* If there are PRs in other projects that are required to test the PR at hand reference those too.

### Keeping it mergeable
In an active project the master branch might change often so keeping it mergeable will guarantee that if the PR has been reviewed it can be merged without the need to do more than just press the merge button.
If the PR needs to be updated by the reviewer then it might happen that there are merge conflicts that need to be resolved. This will take time as the creator knows better how to resolve to a state that everything works again.

### Keeping the Continuous Integration running
Continuous Integration helps to see that every code change still passes all the code quality requirements that were set up to ensure the over all quality of the product.
If they don't pass the reviewer does not need to review, before the checks pass.

Having automated checks in place help all the human brains to not forget anything. :)

## How does this tool help?

TLDR; It gives insight and a quick overview.

This tool was created to check that all PRs fulfill the above mentioned best practices.
It should give an easy overview of the state of the repository and show if there are steps to go to ensure the overall quality of the state of a repository.
Each PR gets penalty points if something is missing.
Those points are summed up and collected over time for your convenience to see that things are going better and displayed in a chart.
Nice!

## What does tool measure

List of stuff that gets penalty points.

In `src/Service/StatsServiceProvider.php`

```php
$app['stats.measureWeight.config'] = $app->factory(
    function () {
        return [
            'age_3' => 10,
            'age_10' => 20,
            'age_unlimited' => 100,
            'not_mergeable' => 10,
            'mergeable_state_not_clean' => 10,
            'no_assignee' => 10,
            'empty_body' => 10,
        ];
    }
);
```

The only weight that is more dynamic is the age of PRs. The points are given for every day the PR is open until day 10 (`$days * $weight`).
After that the value is calculated with `$days * $days * $weight` to indicate that it is open for far too long and immediate action is required.