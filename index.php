<?php
require_once __DIR__ . "/vendor/autoload.php";

$app = new \Silex\Application();
$app['debug'] = true;

$app->register(new \Silex\Provider\HttpFragmentServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['config.github.token'] = '08b18b0e196f60220fcb665eaa98db0222f11ac3';
$app['config.github.repositories'] = [
    'easybib/scholar',
    'easybib/easybib-api',
];

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

            $client = new GuzzleHttp\Client();
            $repositoryUrl = sprintf('https://api.github.com/repos/%s/pulls', $app['github.repository']($repository));
            $request = $client->createRequest('GET', $repositoryUrl);
            $request->addHeader('Authorization', sprintf('token %s', $app['github.token']));
            $res = $client->send($request);

            $pulls = json_decode($res->getBody());

            foreach ($pulls as $key => $pr) {
                $prRequest = $client->createRequest('GET', $pr->url);
                $prRequest->addHeader('Authorization', sprintf('token %s', $app['github.token']));
                $prResponse = $client->send($prRequest);

                $pr = json_decode($prResponse->getBody());
                $pulls[$key]->data = $pr;
            }

            $app['writePrCache']($repository, $pulls);
        }
        return $app['twig']->render('repository.twig', ['pulls' => $pulls]);
})->bind('repository');

$app->get('/repo/{repository}/stats', function ($repository) use ($app) {
        return $app['twig']->render('repository_stats.twig', []);
})->bind('repository_stats');



$app['filename'] = $app->protect(
    function ($repository) {
        $format = (new \DateTime('now'))->format('Y-m-d_H');
        $folderName = sprintf('%s/prLog/%s', __DIR__, $repository);
        
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

$app->run();