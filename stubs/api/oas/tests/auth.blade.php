@switch($action->getMethod())
@case("postSignUp")

@break;
@case("postSignIn")

@break;
@case("postSignOut")

@break;
@case("postRefreshToken")

@break;
@default
@include('api.oas.tests.unknown')
@endswitch
