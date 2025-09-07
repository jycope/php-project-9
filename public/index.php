<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';

use App\Url;
use App\UrlChecks;
use App\UrlChecksRepository;
use App\UrlValidator;
use Slim\Factory\AppFactory;
use DI\Container;
use App\UrlRepository;
use Carbon\Carbon;
use DiDom\Document;
use Dotenv\Dotenv;

session_start();

$container = new Container();
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


$container->set(\PDO::class, function () {
  $databaseUrl = parse_url($_ENV['DATABASE_URL']);
  $username = $databaseUrl['user'];
  $password = $databaseUrl['pass'];
  $host = $databaseUrl['host'];
  $dbName = ltrim($databaseUrl['path'], '/');

  $conn = new \PDO("pgsql:host={$host};port=5432;dbname={$dbName};user={$username};password={$password}");
  $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

  return $conn;
});

$initFilePath = implode('/', [dirname(__DIR__), 'database.sql']);
$initSql = file_get_contents($initFilePath);
$container->get(\PDO::class)->exec($initSql);

$app = AppFactory::createFromContainer($container);
$router = $app->getRouteCollector()->getRouteParser();

$container->set(
  'renderer',
  function () {
    $renderer = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
    $renderer->setLayout('base.phtml');

    return $renderer;
  }
);

$container->set('flash', function () {
  return new \Slim\Flash\Messages();
});

$app->addErrorMiddleware(true, true, true);

$app->get(
  '/',
  function ($request, $response) {
    $params = [
      'url' => new Url(),
      'errors' => []
    ];

    return $this->get('renderer')->render($response, 'new.phtml', $params);
  }
)->setName('urls.create');

$app->post('/urls', function ($request, $response) use ($router) {
  $repo = $this->get(UrlRepository::class);
  $urlData = $request->getParsedBodyParam('url');

  $validator = new UrlValidator();
  $errors = $validator->validate($urlData);

  if (count($errors) === 0) {
    $existing = $repo->findByName($urlData['name']);
    if ($existing) {
      $this->get('flash')->addMessage('success', 'Страница уже существует');
      return $response->withRedirect($router->urlFor('urls.show', ['id' => $existing->getId()]));
    }

    $url = Url::fromArray([$urlData['name']]);
    $repo->save($url);
    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');

    return $response->withRedirect($router->urlFor('urls.show', ['id' => $url->getId()]));
  }

  $params = [
    'url' => Url::fromArray([$urlData['name']]),
    'errors' => $errors
  ];

  return $this->get('renderer')->render($response->withStatus(422), 'new.phtml', $params);
})->setName('urls.store');


$app->get('/urls/{id}', function ($request, $response, $args) use ($router) {
  $repo = $this->get(UrlRepository::class);
  $repoUrlChecks = $this->get(UrlChecksRepository::class);
  $id = $args['id'];

  $url = $repo->find($id);
  $urlChecks = $repoUrlChecks->getChecks($id);

  if (is_null($url)) {
    return $response->write('Page not found')->withStatus(404);
  }

  $messages = $this->get('flash')->getMessages();

  $params = [
    'url' => $url,
    'flash' => $messages,
    'checks' => $urlChecks
  ];

  return $this->get('renderer')->render($response, 'show.phtml', $params);
})->setName('urls.show');

$app->get('/urls', function ($request, $response) {
  $repo = $this->get(UrlRepository::class);
  $urlChecksRepo = $this->get(UrlChecksRepository::class);
  $urls = $repo->getEntities();

  // Получаем данные о последних проверках для каждого URL
  $urlsWithChecks = [];
  foreach ($urls as $url) {
    $lastCheck = $urlChecksRepo->getLastCheckForUrl($url->getId());
    $urlsWithChecks[] = [
      'url' => $url,
      'lastCheck' => $lastCheck
    ];
  }

  $messages = $this->get('flash')->getMessages();

  $params = [
    'urls' => $urlsWithChecks,
    'flash' => $messages
  ];

  return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('urls.index');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router) {
  $repo = $this->get(UrlChecksRepository::class);
  $repoUrl = $this->get(UrlRepository::class);
  $id = $args['url_id'];
  $url = $repoUrl->find($id);
  $name = $url->getName();

  $checkedSite = $repo->getCheckedSite($name);
  $document = new Document($name, true);
  $h1 = $document->has('h1') ? $document->find('h1')[0]->text() : '';
  $title = $document->has('title') ? $document->find('title')[0]->text() : '';
  $meta =
    $document->has('meta[name=description]') ?
    $document->find('meta[name=description]')[0]->getAttribute('content') : '';

  $urlChecks = UrlChecks::fromArray([
    $id,
    Carbon::now(),
    $name,
    $checkedSite->getStatusCode(),
    $title,
    $h1,
    $meta,
  ]);
  $repo->save($urlChecks);
  $this->get('flash')->addMessage('success', 'Страница успешно проверена');

  return $response->withRedirect($router->urlFor('urls.show', ['id' => $urlChecks->getId()]));
});

$app->run();