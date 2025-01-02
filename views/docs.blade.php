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
            background: rgb(37 42 55);
            padding: 0.5em;
        }
    </style>

</head>
<body>

<div class="container">
<div class="col-lg-11 mx-auto pt-3 pt-md-5">
    <header class="row d-flex align-items-center pb-md-3 mb-4 mb-md-5 border-bottom">
        <span class="col d-flex align-items-center text-dark text-decoration-none me-auto user-select-none gap-2">
            <svg fill="currentColor" width="2.5em" height="2.5em" xmlns="http://www.w3.org/2000/svg"
                 viewBox="231 232 276.5 275.49">
                <path
                    d="M504.07 328.344H391.678C388.889 328.344 387.237 325.261 388.779 322.949L444.938 237.395C446.443 235.083 444.791 232 442.038 232H347.485C346.31 232 345.209 232.587 344.585 233.578L231.57 405.75C230.065 408.063 231.717 411.145 234.469 411.145H278.589V411.256H328.765H346.751C349.504 411.256 351.192 414.339 349.651 416.651L293.565 502.095C292.06 504.407 293.712 507.49 296.465 507.49H391.018C392.192 507.49 393.293 506.903 393.917 505.912L506.933 333.74C508.438 331.427 506.786 328.344 504.033 328.344H504.07ZM481.276 347.613L386.503 492.038C385.842 493.029 384.778 493.616 383.603 493.616H322.269C319.479 493.616 317.827 490.533 319.369 488.221L368.848 412.834C369.508 411.843 370.573 411.256 371.747 411.256H401.222C403.828 415.99 408.82 419.257 414.619 419.257C423.098 419.257 429.925 412.393 429.925 403.952C429.925 395.51 423.061 388.647 414.619 388.647C408.526 388.647 403.277 392.244 400.818 397.419H378.978H292.501V397.309H260.2C257.41 397.309 255.758 394.226 257.3 391.913L352.073 247.489C352.734 246.498 353.798 245.91 354.973 245.91H416.307C419.097 245.91 420.749 248.993 419.207 251.306L369.655 326.803C368.994 327.794 367.93 328.381 366.755 328.381H337.942C335.446 323.353 330.27 319.866 324.287 319.866C315.808 319.866 308.981 326.729 308.981 335.171C308.981 343.613 315.845 350.476 324.287 350.476C330.197 350.476 335.226 347.099 337.795 342.218H478.376C481.166 342.218 482.818 345.301 481.276 347.613Z">
                </path>
            </svg>
            <span class="fs-3">
               {{ $title }}
                <span class="text-muted ps-md-2 fs-6 d-block d-md-inline">{ JSON-RPC }</span>
            </span>
        </span>

        <mark class="col-12 col-md-auto px-2 user-select-all mt-2 mt-md-0 p-4 px-md-2 py-md-1">{{ $uri }}</mark>
    </header>

    @foreach($procedures as $procedure)

        <div class="row g-2 g-md-5 mb-3 mb-md-0">
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
                <div class="p-4 {{ $loop->last ? 'rounded-bottom': '' }} {{ $loop->first ? 'rounded-top' : '' }}" style="background: rgb(37 42 55)">
                <div class="row">
                    <div class="col">
                        <p class="user-select-none fw-light text-white opacity-50 mb-1 small">Request:</p>
                        {!! $procedure['request'] ?? '' !!}
                    </div>

                    <div class="col">
                        <p class="user-select-none fw-light text-white opacity-50 mb-1 small">Response:</p>
                        {!! $procedure['response'] ?? '' !!}
                    </div>
                </div>
                </div>
            </div>
        </div>
    @endforeach


    <footer class="pt-5 my-5 text-muted d-flex align-items-center">
        <div class="mx-auto">
            <abbr title="{{ date(DATE_ATOM) }}">Created</abbr> by the <a href="http://sajya.github.io" class="text-dark" target="_blank">Sajya</a>.
        </div>
    </footer>
</div>
</div>

</body>
</html>
