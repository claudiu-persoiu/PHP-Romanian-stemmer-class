<?php
/**
 * Copyright (c) 2009 - 2012 Claudiu Persoiu (http://www.claudiupersoiu.ro/)
 *
 * All rights reserved.
 *
 * This script is free software.
 *
 * DISCLAIMER:
 *
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 *
 * PHP5 Implementation of the Snowball Romanian stemming algorithm
 * (http://snowball.tartarus.org/algorithms/romanian/stemmer.html).
 *
 * Version 0.6
 *
 * Usage:
 *
 *  $stem = RomanianStemmer::Stem($word);
 *
 * The stemmer should work bouth with or without diacritics.
 *
 * NOTE: You must open this document as a UTF-8 file, or you'll override the
 * diacritics.
 *
 *
 * Enjoy, and don't forget to report bugs at claudiu@claudiupersoiu.ro
 */

class RomanianStemmer {

/**
 * Array vocale posibile, caracterele speciale vor fi inlocuite mai jos
 * pentru a nu lucra cu caractere UTF-8
 *
 * @var array
 */
    private static $vocale = array('a','#','&','e','i','%','o','u');

    /**
     * Array cu vocalele rezultate din interesctia cuvantului cu array-ul
     * de mai sus de vocale posibile
     *
     * @var array
     */
    private static $arrV;

    /**
     * Array care contine literele din cuvantul de analizat
     *
     * @var array
     */
    private static $string;

    /**
     * Cuvantul de analizat
     *
     * @var string
     */
    private static $word;

    /**
     * Regiune 1
     *
     * @var string
     */
    private static $R1;

    /**
     * Regiune 2
     *
     * @var string
     */
    private static $R2;

    /**
     * Regiune V
     *
     * @var string
     */
    private static $RV;

    /**
     * Marchiaza daca au fost faculte modificari la pasul 1
     *
     * @var bool
     */
    private static $okPas1;

    /**
     * Marchiaza daca au fost faculte modificari la pasul 2
     *
     * @var bool
     */
    private static $okPas2;



    /**
     * Functia statica in care se vor face prelucrarile
     *
     * @param string $word
     * @return string
     */
    public static function Stem($word) {

        /**
         Legenda inlocuiri:
         # aa
         & i din a
         % ii
         ! sh
         ~ tz
         */

        // resetam variabilele
        // resetarea trebuie facuta mereu la inceput pentru ca aceasta
        // clasa se va apela static
        self::$arrV = array(null);
        self::$string = array(null);
        self::$R1 = NULL;
        self::$R2 = NULL;
        self::$RV = NULL;
        self::$okPas1 = false;
        self::$okPas2 = false;

        // cuvantul de analizat
        self::$word = $word;

        //inlocuim diacriticele care ocupa 2B cu caractere care ocupa
        //1B pentru pasul urmator
        self::$word = str_replace("ă", "#", self::$word);
        self::$word = str_replace("â", "&", self::$word);
        self::$word = str_replace("î", "%", self::$word);
        self::$word = str_replace("ş", "!", self::$word);
        self::$word = str_replace("ţ", "~", self::$word);

        // spargem cuvantul intr-un array
        self::$string = str_split(self::$word);

        // intersectam cuvantul cu vocalele pentru a avea doar vocalele
        // in $arrV
        self::$arrV = array_intersect(self::$string, self::$vocale);

        //i si u dintre vocale se fac caractere mari
        for ($i=1; $i<count(self::$string)-1; $i++) {
            if (isset(self::$arrV[$i-1]) && isset(self::$arrV[$i]) && self::$arrV[$i] === 'i' && isset(self::$arrV[$i+1])) {
                self::$word{$i} = 'I';
            } elseif (isset(self::$arrV[$i-1]) && isset(self::$arrV[$i]) && self::$arrV[$i] === 'u' && isset(self::$arrV[$i+1])) {
                self::$word{$i} = 'U';
            }
        }

        // calculam pozitiile pt R1, R2 si RV
        self::getR();

		/*
		echo self::$word.' - '.self::$R1.' - '.self::$R2.' - '.self::$RV.'<br>';
		*/

        // executam pasul 0 si refacem R-urile
        self::Pas0();
        self::getR();

        // executam pasul 1 de cate ori este nevoie si recalculam R-urile
        while (self::Pas1()) {
            self::getR();
        }

        // executam pasul 2 si recalculam R-urile
        self::Pas2();
        self::getR();

        // daca nu a fost rulat pasul 2 rulam pasul 3
        if ( self::$okPas2 === false) {
            self::Pas3();
            self::getR();
        }

        // executam pasul 4
        self::Pas4();

        // caracterele dintre vocale devin iar mici
        self::$word = str_replace('U','u', self::$word);
        self::$word = str_replace('I','i', self::$word);

        // inlocuim diacriticele la loc
        self::$word = str_replace("#", "ă", self::$word);
        self::$word = str_replace("&", "â", self::$word);
        self::$word = str_replace("%", "î", self::$word);
        self::$word = str_replace("!", "ş", self::$word);
        self::$word = str_replace("~", "ţ", self::$word);

        // intoarcem cuvantul prelucrat
        return self::$word;

    }

    /**
     * Gasire stringuri R1, R2 si RV
     *
     */
    private static function getR() {

        self::$string = str_split(self::$word);

        // refacem intersectia cu vocalele
        self::$arrV = array_intersect(self::$string, self::$vocale);

        $R1Pos = NULL;
        $R2Pos = NULL;
        $RVPos = NULL;


        // gasire pozitia pentru R1 si R2
        for ($i=0; $i<count(self::$string)-1; $i++) {

            if(isset(self::$arrV[$i]) && !isset(self::$arrV[$i+1]) && $R1Pos === NULL) {
                $R1Pos = $i+2;
            } elseif (isset(self::$arrV[$i]) && !isset(self::$arrV[$i+1])  && $R1Pos) {
                $R2Pos = $i+2;
                break;
            }
        }

        // gasire pozitie pentru RV
        if (isset(self::$arrV[0]) && isset(self::$arrV[1])) {
        // urmatoarea consoana
            for ($i = 2; $i < count(self::$string); $i++) {
                if (!isset(self::$arrV[$i])) {
                    $RVPos = $i+1;
                    break;
                }
            }
        } elseif (!isset(self::$arrV[1])) {
        // urmatoare vocala
            for ($i = 2; $i < count(self::$string); $i++) {
                if (isset(self::$arrV[$i])) {
                    $RVPos = $i+1;
                    break;
                }
            }
        } else {
            $RVPos = 3;
        }

        // daca a fost gasita o pozitie pt R1 atunci se calculeaza
        if($R1Pos!=NULL) {
            self::$R1 = substr(self::$word, $R1Pos);
        }

        // daca a fost gasita o pozitie pt R2 atunci se calculeaza
        if($R2Pos!=NULL) {
            self::$R2 = substr(self::$word, $R2Pos);
        }

        // daca pozitia pentru RV nu a fost gasita sau este mai mica
        // sau egala cu cea a cuvantului RV va fi chiar cuvantul
        if(strlen(self::$word)<=$RVPos || $RVPos == NULL) {
            self::$RV = substr(self::$word, strlen(self::$word)-1);
        } else {
            self::$RV = substr(self::$word, $RVPos);
        }

    }

    /**
     * Pasul 0: eliminarea pluralurilor
     *
     */
    private static function Pas0() {

        $arr = array('ul', 'ului');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'delete';
                self::$word = self::rem_str(self::$word, $str);
                self::$R1 = self::rem_str(self::$R1, $str);
                return ;
            }
        }

        $arr = array('aua');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'a';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'a';
                return ;
            }
        }

        $arr = array('ea', 'ele', 'elor');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with e';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'e';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'e';
                return ;
            }
        }

        $arr = array('ii', 'iua', 'iei', 'iile', 'iilor', 'ilor');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with i';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'i';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'i';
                return ;
            }
        }

        $arr = array('ile');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str) && !self::test_str(self::$R1, 'ab'.$str)) {
            // 'replace with i /*if not preceded by ab*/';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'i';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'i';
                return ;
            }
        }

        $arr = array('atei');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with at';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'at';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'at';
                return ;
            }
        }

        $arr = array('a~ie', 'a~ia', 'atie', 'atia');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with a~i';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'a~i';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'a~i';
                return ;
            }
        }
    }

    /**
     * Pas 1: Reducerea combinatilor de sufixuri
     *
     * @return bool
     */
    private static function Pas1() {

        $arr = array('abilitate', 'abilitati', 'abilit#i', 'abilit#~i', 'abilitai');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with abil';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'abil';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'abil';

                self::$okPas1 = true;
                return true;
            }
        }

        $arr = array('ibilitate');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with ibil';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'ibil';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'ibil';

                self::$okPas1 = true;
                return true;
            }
        }

        $arr = array('ivitate', 'ivitati', 'ivit#i', 'ivit#~i', 'ivitai', 'ivitati');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with iv';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'iv';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'iv';

                self::$okPas1 = true;
                return true;
            }
        }

        $arr = array('icitate', 'icitati', 'icit#i', 'icit#~i', 'icitai',
            'icitati', 'icator', 'icatori', 'iciv', 'iciva', 'icive', 'icivi',
            'iciv#', 'iciva', 'ical', 'icala', 'icale', 'icali', 'ical#');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with ic';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'ic';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'ic';

                self::$okPas1 = true;
                return true;
            }
        }

        $arr = array('ativ', 'ativa', 'ative', 'ativi', 'ativ#', 'a~iune',
            'ativa', 'atoare', 'ator', 'atori', '#toare', '#tor', '#tori');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with at';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'at';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'at';

                self::$okPas1 = true;
                return true;
            }
        }

        $arr = array('itiv', 'itiv', 'itive', 'itivi', 'itiv#', 'itiva',
            'i~iune', 'itiune', 'itoare', 'itor', 'itori');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
            // 'replace with it';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'it';
                self::$R1 = self::rem_str(self::$R1, $str);
                self::$R1 .= 'it';

                self::$okPas1 = true;
                return true;
            }
        }

        return  false;
    }

    /**
     * Pas 2: eliminarea sufixelor 'standard'
     *
     * @return bool
     */
    private static function Pas2() {
        $arr = array('at', 'ata', 'at#', 'ati', 'ate', 'ut', 'uta', 'ut#',
            'uti', 'ute', 'it', 'ita', 'it#', 'iti', 'ite', 'ic', 'ica',
            'ice', 'ici', 'ic#', 'ica', 'abil', 'abila', 'abile', 'abili',
            'abil#', 'ibil', 'ibila', 'ibile', 'ibili', 'ibil#', 'oasa',
            'oas#', 'oase', 'os', 'osi', 'o!i', 'ant', 'anta', 'ante', 'anti',
            'ant#', 'ator', 'atori', 'itate', 'itati', 'it#i', 'itai',
            'it#~i', 'iv', 'iva', 'ive', 'ivi', 'iv#');

        foreach ($arr as $str) {
            if (self::test_str(self::$R2, $str)) {
            // 'replace with abil';
                self::$word = self::rem_str(self::$word, $str);
                self::$R2 = self::rem_str(self::$R2, $str);

                self::$okPas2 = true;
                return true;
            }
        }

        $arr = array('iune', 'iuni');

        foreach ($arr as $str) {
            if (self::test_str(self::$R2, '~'.$str)) {
            // 'replace with t';
                self::$word = self::rem_str(self::$word, '~'.$str);
                self::$word .= 't';
                self::$R2 = self::rem_str(self::$R2, '~'.$str);
                self::$R2 .= 't';
                self::$okPas2 = true;
                return true;
            }
        }

        $arr = array('ism', 'isme', 'ist', 'ista', 'iste', 'isti', 'ist#', 'i!ti');

        foreach ($arr as $str) {
            if (self::test_str(self::$R2, $str)) {
            // 'replace with ist';
                self::$word = self::rem_str(self::$word, $str);
                self::$word .= 'ist';
                self::$R2 = self::rem_str(self::$R2, $str);
                self::$R2 .= 'ist';
                self::$okPas2 = true;
                return true;
            }
        }
    }

    /**
     * Pas 3: eliminarea sufixelor de verb
     * acest pas se executa doar daca primele doua nu au inlocuit nimic
     *
     * @return bool
     */
    private static function Pas3() {
        $arr = array('are', 'ere', 'ire', '&re', 'ind', '&nd', 'and', 'indu',
            '&ndu', 'andu', 'eze', 'easc#', 'easca', 'ez', 'ezi', 'eaz#',
            'eaza', 'esc', 'e!ti', 'esti', 'e!te', 'este', '#sc', 'asc',
            '#!ti', 'asti', '#!te', 'aste', 'am', 'ai', 'au', 'eam', 'eai',
            'ea', 'ea~i', 'eau', 'iam', 'iai', 'ia', 'ia~i', 'iati', 'iau',
            'ui', 'a!i', 'asi', 'ar#m', 'aram', 'ar#~i', 'arati', 'ar#',
            'ara', 'u!i', 'usi', 'ur#m', 'uram', 'ur#~i', 'urati', 'ur#',
            'ura', 'i!i', 'isi', 'ir#m', 'iram', 'ir#~i', 'irati', 'ir#',
            'ira', '&i', 'ai', '&!i', 'asi', '&r#m', 'aram', '&r#~i', 'arati',
            '&r#', 'ara', 'asem', 'ase!i', 'asesi', 'ase', 'aser#m', 'aseram',
            'aser#~i', 'aserati', 'aser#', 'asera', 'isem', 'ise!i', 'isesi',
            'ise', 'iser#m', 'iseram', 'iser#~i', 'iserati', 'iser#', 'isera',
            '&sem', 'asem', '&se!i', 'asesi', '&se', 'ase', '&ser#m', 'aseram',
            '&ser#~i', 'aserati', '&ser#', 'asera', 'usem', 'use!i', 'usesi',
            'use', 'user#m', 'useram', 'user#~i', 'userati', 'user#', 'usera');

        foreach ($arr as $str) {
            if (self::test_str(self::$RV, $str) && (!isset(self::$arrV[(strlen(self::$word)-strlen($str)-1)]) || self::$arrV[(strlen(self::$word)-strlen($str)-1)] == 'u')) {

                self::$word = self::rem_str(self::$word, $str);
                self::$RV = self::rem_str(self::$RV, $str);
                return true;
            }
        }

        $arr = array('#m', 'am', 'a~i', 'asi', 'em', 'e~i', 'eti', 'im',
            'i~i', 'iti', '&m', 'am', '&~i', 'ati', 'se!i', 'sesi', 'ser#m',
            'seram', 'ser#~i', 'serati', 'ser#', 'sera', 'sei', 'se', 'sesem',
            'sese!i', 'sesesi', 'sese', 'seser#m', 'seseram', 'seser#~i',
            'seserati', 'seser#', 'sesera');

        foreach ($arr as $str) {
            if (self::test_str(self::$RV, $str)) {
            // delete
                self::$word = self::rem_str(self::$word, $str);
                self::$RV = self::rem_str(self::$RV, $str);
                return true;
            }
        }
    }

    /**
     * Pas 4: eliminarea vocalelor finale
     *
     * @return bool
     */
    private static function Pas4() {

        $arr = array('a', 'ie', 'e', 'i', '#', 'a');

        foreach ($arr as $str) {
            if (self::test_str(self::$R1, $str)) {
                self::$word = self::rem_str(self::$word, $str);
                self::$R1 = self::rem_str(self::$R1, $str);

                return true;
            }
        }

    }


    /**
     * verificarea sufixului
     *
     * @param string $R1
     * @param string $str
     * @return bool
     */
    private static function test_str($string, $str) {
        $len = strlen($str);
        if ($str == substr($string, $len*(-1), $len)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * eliminarea unui string de la finalul unui string mare
     *
     * @param sring $string
     * @param string $rem
     * @return string
     */
    private static function rem_str($string, $rem) {
        $len = strlen($rem);
        if (substr($string, $len*(-1), $len) == $rem) {
            return substr($string, 0, (strlen($string)-$len));
        } else {
            return $string;
        }
    }
}
?>