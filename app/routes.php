<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Slim\Views\PhpRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET'];); // Replace with your Stripe Test Secret Key

return function (App $app) {
    $renderer = new PhpRenderer(__DIR__ . '/../templates');

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/pay', function (Request $request, Response $response) use ($renderer) {
        return $renderer->render($response, "pay.php", [
            'publishableKey' => $_ENV['STRIPE_PUBLISHABLE']; // Replace with your Publishable Key
        ]);
    });

    $app->post('/charge', function (Request $request, Response $response) use ($renderer) {
        $params = (array)$request->getParsedBody();
        $token = $params['stripeToken'] ?? null;
        $email = $params['stripeEmail'] ?? null;
        $message = '';

        try {
            $charge = \Stripe\Charge::create([
                'amount' => 1000,
                'currency' => 'usd',
                'description' => 'Demo Payment',
                'source' => $token,
                'receipt_email' => $email,
            ]);
            $message = 'Payment successful! Charge ID: ' . $charge->id;
        } catch (\Stripe\Exception\CardException $e) {
            $message = 'Payment failed: ' . $e->getError()->message;
        }

        return $renderer->render($response, "result.php", ['message' => $message]);
    });
};
