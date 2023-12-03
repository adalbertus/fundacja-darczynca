# Aplikacja
Aplikacja powstała z potrzeby automatycznego rozpoznawania darczyńców zasilających konto fundacji.

## Autor
Autorem aplikacji jest [Wojciech Pietkiewicz](https://github.com/adalbertus). Zgadzam się na wszelkie zmiany w kodzie i używaniego w jakikolwiek sposób.
Będzie mi miło jak pojawi się wzmianka o moim autorstwie 😊.

## Silnik aplikacji
Aplikacja jest napisana w PHP z wykorzysataniem [Symfony](https://symfony.com/) i wszystkim co Symfony proponuje i zapewnia (wraz z pakietem Symfony UX).
Dodatkowo aplikacja wykorzystuje [Bootstrap](https://getbootstrap.com/docs/5.3/getting-started/introduction/) oraz [Fontawesome](https://fontawesome.com/) oraz [Google Fonts](https://fonts.google.com/). 

# Instalacja
Należy sprawdzić wymagania systemowe dla Symfony.

Jeżeli wymagania są spełnione to wystarczy pobrać zawartość repozytorium i wykonać polecenie:
```sh
# zakładam, że aplikacja będzie znajdowała się w katalogu app
# natomiast serwer WWW jest tak skonfigurowany, że korzysta z app/public
git clone git@github.com:adalbertus/fundacja-darczynca.git app
```
Następnie należy odpowiednio skonfigurować aplikację modyfikując plik `.env`. Należy zmienić:
- `APP_NAME` - nazwa fundacji
- `APP_CONTACT_EMAIL` - adres e-mail
- `APP_NOREPLY_EMAIL` - adres e-mail (może być ten sam co adres fundacji)
- `APP_WEBPAGE_URL` - adres URL strony z aplikacją
- `APP_ENV` - typ środowiska: produkcyjne wtedy ustawiamy wartość `prod`, developerskie `dev`
- `APP_SECRET`
- `DATABASE_URL` - połączenie do bazy danych (np. zgodnie z `mysqli://user:secret@localhost/mydb`)
- `MAILER_DSN` - połączenie do serwera poczty, szczegóły tutaj: https://symfony.com/doc/current/mailer.html (najprostsze `smtp://user:pass@smtp.example.com:25`)

Następnym krokiem jest zainstalowanie Symfony:
```sh
# UWAGA, jeżeli ustawiliśmy APP_ENV na dev wtedy należy usunąć opcję --no-dev
composer install --no-dev --optimize-autoloader

# utworzenie tabel w bazie danych
bin/console doctrine:migrations:migrate
```
Na serwerze produkcyjnym nie jest zalecane posiadanie zainstalowanego yarn'a
dlatego najlepiej na środowisku developerskim przygotować wszystkie zasoby CSS oraz JavaScript
używany jest do tego `yarn`:
```sh
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

# zbudowanie całości (można wcześniej usunąć zawartość public/build)
yarn build

# następnie musimy przenieść całość public/build na serwer
```

Utworzyć podstawową konfigurację w bazie danych:
```sql
INSERT INTO `description_regexp` (`expression`, `category`, `sub_category`, `comment`, `created_at`, `updated_at`) 
VALUES ('cele statutowe', 'darowizna', 'darowizna', NULL, NOW(), NOW());

-- UWAGA - zmienić wg. uznania adres e-mail
-- UWAGA - hasło koniecznie należy zmienić po zalogowaniu się (domyślnie ustawione hasło to: haslo)
INSERT INTO `user` (`email`, `roles`, `password`, `is_verified`, `is_active`, `created_at`, `updated_at`)
VALUES ('admin@example.com', '["ROLE_ADMIN", "ROLE_USER"]', '$2y$13$lEPa0D6wP.QHxEzmzprB4Oz0S2pz7iXKCxXXGGoAVvPlvm36mMpVC', 1, 1, NOW(), NOW());

```

# Podejrzenie danych testowych w czasie UnitTests
Jeżeli chcemy podejrzeć jakie dane zostały w teście, należy ręcznie zatwierdzić transakcję w teście albo w serwise:
```php
        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
        die;
```