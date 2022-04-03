<?php

namespace Avocado\AvocadoView;

class View {
    private string $filename;
    private array $params;
    private string $parsedView = "";

    private string $variablePattern = '/@var\((.*?)\)/';
    private string $eachPattern = '@each\((.*?)\)\)\s*{\s*(.*)\s*\s*}/gm';

    /**
     * @param string $filename
     * @param array $params
     */
    public function __construct(string $filename, ?array $params = []) {
        $this->filename = $filename;
        $this->params = $params;
        $this->readView();
    }

    /**
     * @throws AvocadoViewNotFoundException
     * @throws AvocadoViewException
     */
    private function validateView() {
        if (!file_exists($this->filename)) {
            throw new AvocadoViewNotFoundException("$this->filename view was not found");
        }

        $fileInfo = pathinfo($this->filename);
        $fileExtension = $fileInfo['extension'];

        if ($fileExtension !== 'avocado') {
            throw new AvocadoViewException("View must have .avocado extension, passed $fileExtension");
        }
    }

    private function parseLineToHTML(string $line) {
        preg_match_all($this->variablePattern, $line, $variableMatches);

        // variables
        if (!empty($variableMatches[0])) {
            $statements = $variableMatches[0];
            $variablesNames = $variableMatches[1];

            for ($statementIndex = 0; $statementIndex < count($statements); $statementIndex++) {
                $statement = $statements[$statementIndex];
                $variable = trim($variablesNames[$statementIndex]);

                $isVariableExists = array_key_exists($variable, $this->params);

                if ($isVariableExists) {
                    $line = str_replace($statement, $this->params[$variable], $line);
                } else {
                    $line = str_replace($statement, "UNDEFINED_VARIABLE_NAME", $line);
                }
            }

            $this->parsedView .= $line;
            return;
        }
        $this->parsedView .= $line;
    }

    /**
     * @throws AvocadoViewNotFoundException
     * @throws AvocadoViewException
     */
    private function readView() {
        $this->validateView();

        $fileContent = fopen($this->filename, "r");

        while (($line = fgets($fileContent))) {
            $this->parseLineToHTML($line);
        }
    }
}
