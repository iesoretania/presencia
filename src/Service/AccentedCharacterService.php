<?php


namespace App\Service;


class AccentedCharacterService
{
    public function removeAccents(string $in): string
    {
        $search = explode(",","Ç,ç,æ,œ,Á,á,É,é,Í,í,Ó,ó,Ú,ú,À,à,È,è,Ì,ì,Ò,ò,Ù,ù,Ä,ä,Ë,ë,Ï,ï,Ö,ö,Ü,ü,Ÿ,ÿ,Â,â,Ê,ê,Î,î,Ô,ô,Ü,û,å,e,i,ø,u,Ñ,ñ");
        $replace = explode(",","C,c,ae,oe,A,a,E,e,I,i,O,o,U,u,A,a,E,e,I,i,O,o,U,u,A,a,E,e,I,i,O,o,U,u,Y,y,A,a,E,e,I,i,O,o,U,u,a,e,i,o,u,N,n");
        return str_replace($search, $replace, $in);
    }
}
