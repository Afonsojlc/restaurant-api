<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se o utilizador NÃO for admin, barra a entrada com um erro 403
        if (!$request->user()?->isAdmin()) {
            return response()->json(['message' => 'Acesso reservado ao proprietario.'], 403);
        }

        // Se for admin, deixa passar para o passo seguinte
        return $next($request);
    }
}
