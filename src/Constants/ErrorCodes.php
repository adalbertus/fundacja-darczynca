<?php
namespace App\Constants;

use App\Exception\ErrorCodeDoesNotExistsException;

class ErrorCodes
{
    public const ERROR_CODE = 'error_code';
    public const BRAK_UPRAWNIEN = 'ERROR:001';
    public const NIE_ZNALEZIONO_STRONY = 'ERROR:002';
    public const BRAK_UPRAWNIEN_TEXT = 'Brak uprawnień.';
    public const NIE_ZNALEZIONO_STRONY_TEXT = 'Nie znaleziono strony.';

    public const BRAK_DARCZYNCY = "ERROR:003";
    public const BRAK_DARCZYNCY_TEXT = "Należy ustawić darczyńcę.";

    public const DODATKOWE_POLA_USTAWIONE = "ERROR:004";
    public const DODATKOWE_POLA_USTAWIONE_TEXT = "Dodatkowe pola (jak np. darczyńca) muszą być puste.";
    public const BLEDNIE_WYBRANA_PODKATEGORIA = "ERROR:005";
    public const BLEDNIE_WYBRANA_PODKATEGORIA_TEXT = "Błędnie wybrana podkategoria.";
    public const PRZYNAJMNIEJ_JEDNA_FRAZA = 'ERROR:006';
    public const PRZYNAJMNIEJ_JEDNA_FRAZA_TEXT = 'Musi istnieć przynajmniej jedna fraza wyszukiwania.';




    public static function message(string $errorCode): string
    {
        $error = [];
        $error[self::BRAK_UPRAWNIEN] = self::BRAK_UPRAWNIEN_TEXT;
        $error[self::NIE_ZNALEZIONO_STRONY] = self::NIE_ZNALEZIONO_STRONY_TEXT;
        $error[self::BRAK_DARCZYNCY] = self::BRAK_DARCZYNCY_TEXT;
        $error[self::DODATKOWE_POLA_USTAWIONE] = self::DODATKOWE_POLA_USTAWIONE_TEXT;
        $error[self::BLEDNIE_WYBRANA_PODKATEGORIA] = self::BLEDNIE_WYBRANA_PODKATEGORIA_TEXT;


        if (array_key_exists($errorCode, $error)) {
            return $error[$errorCode];
        } else {
            throw new ErrorCodeDoesNotExistsException("Error code {$errorCode} does not exists");
        }
    }


}