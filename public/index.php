<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Url;
use App\UrlValidator;
use Slim\Factory\AppFactory;
use DI\Container;
use App\UrlRepository;

session_start();

$container = new Container();

$container->set(\PDO::class, function () {
  $conn = new \PDO('pgsql:host=localhost;port=5432;dbname=hexlet;user=postgres;password=TSo280LyvLlSuu[');
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

    return $this->get('renderer')->render($response, 'index.phtml', $params);
  }
)->setName('urls.create');

$app->post('/urls', function ($request, $response) use ($router) {
  $repo = $this->get(UrlRepository::class);
  $urlData = $request->getParsedBodyParam('url');

  $validator = new UrlValidator();
  $errors = $validator->validate($urlData);

  if (count($errors) === 0) {
    $url = Url::fromArray([$urlData['name']]);
    $repo->save($url);
    $this->get('flash')->addMessage('success', 'URL был успешно добавлен');

    return $response->withRedirect($router->urlFor('urls.show', ['id' => $url->getId()]));
  }
});


$app->get('/urls/{id}', function ($request, $response, $args) use ($router) {
  $repo = $this->get(UrlRepository::class);
  // $urlData = $request->getParsedBodyParam('url');
  $id = $args['id'];

  $url = $repo->find($id);

  if (is_null($url)) {
    return $response->write('Page not found')->withStatus(404);
  }

  $messages = $this->get('flash')->getMessages();

  $params = [
    'url' => $url,
    'flash' => $messages
  ];

  return $this->get('renderer')->render($response, 'show.phtml', $params);
})->setName('urls.show');;

$app->run();
