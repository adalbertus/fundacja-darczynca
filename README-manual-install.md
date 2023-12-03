# Instalacja aplikacji
Najlepiej instalować aplikację bezpośrednio klonując repozytorium. Jeżeli jednak z jakiegoś powodu trzeba odtworzyć framework Symfony ręcznie
to można to zrobić poniższymi komendami:
``` bash
symfony new fundacja --version="6.3.*" --webapp
cd fundacja
composer require symfony/apache-pack debug twig symfony/webpack-encore-bundle symfony/asset symfony/twig-pack symfony/validator symfony/uid
composer require symfony/http-foundation twig/string-extra symfonycasts/reset-password-bundle symfony/string symfony/orm-pack
composer require symfony/security-bundle symfonycasts/verify-email-bundle symfony/mailer symfony/validator symfony/mime symfony/security-csrf
composer require stof/doctrine-extensions-bundle twig/intl-extra twig/extra-bundle twig/cssinliner-extra
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter pagerfanta/twig
composer require symfony/doctrine-messenger
composer require league/csv:^9.0

composer require --dev symfony/maker-bundle symfony/test-pack dama/doctrine-test-bundle doctrine/doctrine-fixtures-bundle orm-fixtures zenstruck/foundry
# do rozważenia czy warto...
# composer require easycorp/easyadmin-bundle

composer require symfony/stimulus-bundle symfony/ux-twig-component symfony/ux-turbo symfony/ux-autocomplete symfony/ux-chartjs symfony/ux-dropzone
yarn install
yarn add file-loader@^6.0.0 --dev
yarn add axios --dev
yarn add "hotkeys-js@>= 3" --dev
yarn add "@popperjs/core@^2.11.8"
yarn add stimulus-use --dev
yarn add stimulus-clipboard
yarn add stimulus-animated-number
yarn add bootstrap --dev
yarn add sass-loader@^13.0.0 sass --dev
yarn add @fontsource/roboto-condensed --dev
yarn add @fortawesome/fontawesome-free --dev
```
