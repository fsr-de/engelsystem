<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Http\Request;
use Engelsystem\Database\Db;

class ApiController extends BaseController
{
    public function __construct(protected Response $response)
    {
    }

    public function index(): Response
    {
        return $this->response
            ->setStatusCode(501)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode(['error' => 'Not implemented']));
    }

    /**
     * @return Response
     */
    public function usershifts(Request $request): Response
    {
        $email = $request->getAttribute('email');

        $count = (Db::selectOne(
            '
            SELECT COUNT(*) AS count FROM shift_entries, users
            WHERE shift_entries.user_id = users.id 
            AND users.email = ?;
            ', [$email]
        ));

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode(['count' => $count['count']]));
    }
}
