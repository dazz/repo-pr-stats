<?php
require_once __DIR__ . "/../vendor/autoload.php";

$app = new \Silex\Application();
$app['debug'] = true;
$app['rootDir'] = sprintf('%s/..', __DIR__);

$app->register(new \Silex\Provider\HttpFragmentServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $app['rootDir'].'/views',
));
$app->register(new \Dazz\PrStats\Service\RepositoryServiceProvider);
$app->register(new \Dazz\PrStats\Service\StorageServiceProvider);
$app->register(new \Dazz\PrStats\Service\StatsServiceProvider);

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', []);
})->bind('index');
$app->get('/navigation', function () use ($app) {
    return $app['twig']->render('navigation.twig');
})->bind('navigation');
$app->get('/sidebar', function () use ($app) {
    return $app['twig']->render('sidebar.twig', ['repositories' => $app['github.repositories']]);
})->bind('sidebar');

$app->get('/repo/{repositorySlug}', function ($repositorySlug) use ($app) {
//        $record = $app['storage']->getLastRecord($repositorySlug);
        $record = $app['storage']->getRecord($repositorySlug, '2014-08-19_00');
        $recordStats = $app['stats']->getRecordStats($record);

        return $app['twig']->render(
            'repository.twig',
            [
                'record'      => $record,
                'recordStats' => $recordStats,
                'repository'  => $repositorySlug
            ]
        );
})->bind('repository');

$app->post('/repo/{repositorySlug}', function ($repositorySlug) use ($app) {
        $app['repository.recorder']->createRecord($repositorySlug);
        return $app->redirect($app['url_generator']->generate('repository', ['repositorySlug' => $repositorySlug]));
    })->bind('repository_createDump');

$app->get('/repo/{repositorySlug}/stats', function ($repositorySlug) use ($app) {
        $stats = $app['stats']->getAllRepositoryStats($repositorySlug);
        return $app['twig']->render('repository_stats.twig', ['stats' => $stats]);
})->bind('repository_stats');


$configFile = __DIR__ . '/config.php';
if (file_exists($configFile) == false) {
    throw new \Exception($configFile . ' does not exist!');
}
require_once __DIR__ . '/config.php';

$app->run();
