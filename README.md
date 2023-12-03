# fundacja
Aplikacja do zarządzania funadcją

# Podejrzenie danych testowych w czasie UnitTests
Jeżeli chcemy podejrzeć jakie dane zostały w teście, należy ręcznie zatwierdzić transakcję w teście albo w serwise:
```php
        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
        die;
```