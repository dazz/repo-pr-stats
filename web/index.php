<?php
require_once __DIR__ . "/../vendor/autoload.php";

$app = new \Silex\Application();
$app['debug'] = true;
$app['rootDir'] = sprintf('%s/..', __DIR__);

$app->register(new \Silex\Provider\HttpFragmentServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $app['rootDir'].'/views',
));

$app['github.token'] = $app->factory(
    function () use ($app) {
        if ($app->offsetExists('config.github.token') && !empty($app['config.github.token'])) {
            return $app['config.github.token'];
        }
        throw new \UnexpectedValueException('config.github.token is missing');
    }
);
$app['github.repositories'] = $app->factory(
    function () use ($app) {
        if ($app->offsetExists('config.github.repositories') && !empty($app['config.github.repositories'])) {
            return array_combine(
                array_map(
                    function ($repository) {
                        return str_replace('/', '-', $repository);
                    },
                    $app['config.github.repositories']
                ),
                $app['config.github.repositories']
            );
        }
        throw new \UnexpectedValueException('config.github.repositories are missing');
    }
);

$app['github.repository'] = $app->protect(
    function ($repository) use ($app) {
        if (isset($app['github.repositories'][$repository])) {
            return $app['github.repositories'][$repository];
        }

        throw new \UnexpectedValueException('repository is not defined in config.github.repositories');
    }
);

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', []);
})->bind('index');
$app->get('/navigation', function () use ($app) {
    return $app['twig']->render('navigation.twig');
})->bind('navigation');
$app->get('/sidebar', function () use ($app) {
    return $app['twig']->render('sidebar.twig', ['repositories' => $app['github.repositories']]);
})->bind('sidebar');

$app->get('/repo/{repository}', function ($repository) use ($app) {
        $pulls = $app['readPrCache']($repository);
        if(empty($pulls)) {

            $pulls = $app['getPulls']($repository);

            foreach ($pulls as $key => $pr) {
                $pulls[$key]->data = $app['getPullDetail']($pr->url);
                $pulls[$key]->data_statuses = $app['getPullStatus']($pulls[$key]->data->statuses_url);
                if (count($pulls[$key]->data_statuses) > 0) {
                    $pulls[$key]->data_statuses_last = $pulls[$key]->data_statuses[0];
                }
                $pulls[$key]->data_weight = $app['calculatePullWeight']($pr);
            }

            $app['writePrCache']($repository, $pulls);
        }
        return $app['twig']->render('repository.twig', ['pulls' => $pulls, 'repository' => $repository]);
})->bind('repository');

$app->get('/repo/{repository}/stats', function ($repository) use ($app) {
        $repoDir = sprintf('%s/%s', $app['cacheDir'], $repository);
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->in($repoDir)->sortByName();
        $stats = [];
        foreach ($finder as $file) {
            /** @var \SplFileInfo $file */
            $stat = [];
            $pullRequests = json_decode(file_get_contents($file->getRealpath()));

            $stat['countPullRequests'] = count($pullRequests);

            list($filename, $extension) = explode('.', $file->getRelativePathname());
            list($repo, $date, $hour) = explode('_', $filename);

            $stat['filename'] = $file->getRelativePathname();
            $stat['date'] = $date;
            $stat['hour'] = $hour;

            $key = sprintf('%s-%s', $date, $hour);

            $stat['agePullRequests'] = 0;
            $stat['weight'] = 0;
            foreach ($pullRequests as $pull) {
                $data = $pull->data;
                $days = (new \DateTime($data->created_at))->diff(new \DateTime($date))->format('%a');
                if ($days > $stat['agePullRequests']) {
                    $stat['agePullRequests'] = $days;
                }
                $stat['weight'] += $app['calculatePullWeight']($pull, $date);
            }

            $stats[$key] = $stat;
        }

        return $app['twig']->render('repository_stats.twig', ['stats' => $stats]);
})->bind('repository_stats');

$app['getPulls'] = $app->protect(
    function ($repository) use ($app) {
        $client = new GuzzleHttp\Client();
        $repositoryUrl = sprintf('https://api.github.com/repos/%s/pulls', $app['github.repository']($repository));
        $request = $client->createRequest('GET', $repositoryUrl);
        $request->addHeader('Authorization', sprintf('token %s', $app['github.token']));
        $res = $client->send($request);

        return json_decode($res->getBody());
    }
);

$app['getPullDetail'] = $app->protect(
    function ($detailUrl) use ($app) {
        $client = new GuzzleHttp\Client();
        $request = $client->createRequest('GET', $detailUrl);
        $request->addHeader('Authorization', sprintf('token %s', $app['github.token']));
        $res = $client->send($request);

        return json_decode($res->getBody());
    }
);
$app['getPullStatus'] = $app->protect(
    function ($statusesUrl) use ($app) {
        $client = new GuzzleHttp\Client();
        $request = $client->createRequest('GET', $statusesUrl);
        $request->addHeader('Authorization', sprintf('token %s', $app['github.token']));
        $res = $client->send($request);

        return json_decode($res->getBody());
    }
);

$app['cacheDir'] = sprintf('%s/prLog', $app['rootDir']);

$app['filename'] = $app->protect(
    function ($repository) use ($app) {
        $format = (new \DateTime('now'))->format('Y-m-d_H');
        $folderName = sprintf('%s/%s', $app['cacheDir'], $repository);
        
        if (is_dir($folderName) == false) {
            mkdir($folderName, 0777, true);
        }

        return sprintf('%s/%s_%s.json', $folderName, $repository, $format);
    }
);

$app['readPrCache'] = $app->protect(
    function ($repository) use ($app) {
        $pulls = [];
        $filename = $app['filename']($repository);
        if (file_exists($filename) == true) {
            $pulls = json_decode(file_get_contents($filename));
        }

        return $pulls;
    }
);

$app['writePrCache'] = $app->protect(
    function ($repository, $pulls) use ($app) {
        $filename = $app['filename']($repository);
        if (file_exists($filename) == false) {
            file_put_contents($filename, json_encode($pulls, JSON_PRETTY_PRINT));
        }
    }
);

$app['calculatePullWeight'] = $app->protect(
    function ($pull, $dateString) use ($app) {
        $weight = 0;

        $days = (new \DateTime($pull->data->created_at))->diff(new \DateTime($dateString))->format('%a');
        $weight += $app['getMeasureWeights']['age'] * $days;

        $mergeable = $pull->data->mergeable ? 'yes' : 'no';

        $weight += $app['getMeasureWeights']['mergeable'][$mergeable];

        $weight += $app['getMeasureWeights']['mergeable_state'][$pull->data->mergeable_state];

        return $weight;
    }
);

$app['getMeasureWeights'] = $app->factory(
    function () {
        return [
            'age' => 10,
            'mergeable' => [
                'yes' => 0,
                'no' => 10
            ],
            'mergeable_state' => [
                'unknown' => 10,
                'unstable' => 10,
                'dirty' => 10,
                'clean' => 0,
            ],
        ];
    }
);

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile) == false) {
    throw new \Exception($configFile . ' does not exist!');
}
require_once __DIR__ . '/config.php';

$app->run();