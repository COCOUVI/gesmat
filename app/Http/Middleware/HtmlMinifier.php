<?php

declare(strict_types=1);

// namespace App\Http\Middleware;

// use Closure;
// use voku\helper\HtmlMin;
// use Illuminate\Http\Request;

// class HtmlMinifier
// {
//     public function handle(Request $request, Closure $next)
//     {
//         $response = $next($request);

//         // Ne pas appliquer si ce n’est pas du HTML
//         if (
//             $response instanceof \Illuminate\Http\Response &&
//             strpos($response->headers->get('Content-Type'), 'text/html') !== false
//         ) {
//             $htmlMin = new HtmlMin();

//             // Active les optimisations que tu veux
//             $htmlMin->doOptimizeAttributes(true);
//             $htmlMin->doRemoveComments(true);
//             $htmlMin->doSumUpWhitespace(true);
//             $htmlMin->doRemoveSpacesBetweenTags(true);
//             $htmlMin->doRemoveOmittedHtmlTags(true);

//             // Minifie le contenu HTML
//             $minified = $htmlMin->minify($response->getContent());
//             $response->setContent($minified);
//         }

//         return $response;
//     }
// }
