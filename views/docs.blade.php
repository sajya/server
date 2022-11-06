<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

    <title>{{ $title }} - JSON-RPC Documentation</title>
    <meta name="description" content="JSON-RPC Documentation">
    <link rel="icon" type="image/png" sizes="32x32" href="https://sajya.github.io/favicon-32x32.png">
    <meta name="robots" content="noindex">

    <style>
        ul {
            list-style: circle;
        }

        code {
            display: block;
            background: #f8f8f8;
            padding: 0.5em;
        }
    </style>

</head>
<body>

<div class="container">
<div class="col-lg-11 mx-auto pt-3 pt-md-5">
    <header class="row d-flex align-items-center pb-md-3 mb-4 mb-md-5 border-bottom">
        <span class="col d-flex align-items-center text-dark text-decoration-none me-auto user-select-none">
            <img alt="Starter template" src="https://sajya.github.io/assets/img/logo.svg" width="45" class="me-2">
            <span class="fs-3">
               {{ $title }}
                <span class="text-muted ps-md-2 fs-6 d-block d-md-inline">{ JSON-RPC } Documentation</span>
            </span>
        </span>

        <mark class="col-12 col-md-auto px-2 user-select-all mt-2 mt-md-0">{{ $uri }}</mark>
    </header>

    @foreach($procedures as $procedure)

        <div class="row g-2 g-md-5">
            <div class="col-12 col-md-3 pe-md-0">
                <h4 class="user-select-all mb-3">
                    {{ $procedure['name'] }}
                    <span class="d-block text-muted">{{ $procedure['delimiter'] }}{{ $procedure['method'] }}</span>
                </h4>
                <p>
                    {{ $procedure['description'] ?? '' }}
                </p>

            </div>

            <div class="col">
                <p class="user-select-none fw-light text-muted mb-1">Request:</p>
                {!! $procedure['request'] ?? '' !!}
            </div>

            <div class="col">
                <p class="user-select-none fw-light text-muted mb-1">Response:</p>
                {!! $procedure['response'] ?? '' !!}
            </div>
        </div>

        @if (!$loop->last)
            <hr class="col-3 col-md-2 mb-5">
        @endif
    @endforeach


    <footer class="pt-5 my-5 text-muted border-top d-flex align-items-center">
        <div class="me-auto">
            <a href="https://www.jsonrpc.org/specification"
               class="d-flex align-items-center text-dark text-decoration-none" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1"
                     viewBox="0 0 16 16">
                    <path
                        d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
                    <path
                        d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                </svg>

                Specification
            </a>
        </div>

        <div>
            Created by the <a href="http://sajya.github.io" class="text-dark" target="_blank">Sajya</a>.
        </div>
    </footer>
</div>
</div>

</body>
</html>
