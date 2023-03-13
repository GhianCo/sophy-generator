<?php

namespace SophyGenerator\Factory;

use Psr\Container\ContainerInterface;

class SophyGeneratorService
{
    private $dbConn;
    private $database;

    private $targetExportApp = __DIR__ . '/../../../../../app/';
    private $targetExportConfig = __DIR__ . '/../../../../../config/';
    private $targetExportSrc = __DIR__ . '/../../../../../src/';

    private $sourceFactory = __DIR__ . '/../../src/Factory/';

    private $allTables = array();

    public function __construct($dbConn, $database)
    {
        $this->dbConn = $dbConn;
        $this->database = $database;
    }

    public function generateStructure()
    {
        $this->validateHasDatabase();

        $this->updateFilesRequiredToApp();

        $this->generateControllerFilesByTable();
        $this->generateEntityFilesByTable();
        $this->generateDTOFilesByTable();
        $this->generateExceptionFilesByTable();
        $this->generateRepositoryFilesByTable();
        $this->generateRouteFilesByTable();
        $this->generateServiceContainerFilesByTable();
        //$this->generateServiceFilesByTable();
    }

    function validateHasDatabase()
    {
        $query = $this->dbConn->query('SHOW TABLES');
        $tables_in_db = $query->fetchAll();
        $db = "Tables_in_" . $this->database;
        $tableList = array();

        foreach ($tables_in_db as $table) {
            $query = $this->dbConn->query('describe ' . $table[$db]);
            $dataTable = $query->fetchAll();
            $fieldList = array();

            $i = 0;

            foreach ($dataTable as $fVal) {
                $data = new \stdClass();
                $data->key = $fVal['Field'];
                $data->data = $fVal;
                // put it into the array
                $fieldList[$i] = $data;
                $i++;
            }
            // after completing the loop, put the results into the table list array
            $tableList[$table[$db]] = $fieldList;
        }
        $this->allTables = $tableList;
    }

    function doQuery($sqlStatment, $indexField = 'auto')
    {
        // perform the query and put it into a temporary variable
        $dbQuery = $this->dbConn::select($sqlStatment);
        // create an array of queried objects
        $dataSet = array();

        // this variable is used for automatic counter
        $i = 0;

        // Structuring the internal table for the data
        // loop through the records retrieved
        while ($rows = $dbQuery->fetch()) {

            if ($indexField != 'auto') {                    // if not automatic indexing

                // use the specified indexField specified
                // as index pointer and assign the value retrived from the database
                $dataSet[$rows[$indexField]] = $rows;

            } else {                                        // if automatic indexing
                // assign current index count as a pointer for the current
                // data retrived form the databse.
                $dataSet[$i] = $rows;
                // increase index counter
                $i++;
            }

        }

        return $dataSet;
    }


    function updateFilesRequiredToApp()
    {

        /**
         * All repositories
         */

        $__appRepositories = PHP_EOL;
        $__appRepositories .= PHP_EOL;


        $__appRepositories .= 'use DI\ContainerBuilder;' . PHP_EOL;

        foreach ($this->allTables as $index => $table) {
            $__appRepositories .= "use App\\" . ucfirst($index) . "\Domain\\I" . ucfirst($index) . "Repository;" . PHP_EOL;
            $__appRepositories .= "use App\\" . ucfirst($index) . "\Infrastructure\\" . ucfirst($index) . "RepositoryMysql;" . PHP_EOL;
        }

        $__appRepositories .= PHP_EOL;

        $__appRepositories .= 'return function (ContainerBuilder $containerBuilder) {' . PHP_EOL;
        $__appRepositories .= '    $containerBuilder->addDefinitions([' . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__appRepositories .= '        I' . ucfirst($index) . 'Repository::class => \DI\autowire(' . ucfirst($index) . 'RepositoryMysql::class)->method(\'setTable\', \'' . $index . '\'),' . PHP_EOL;
        }
        $__appRepositories .= '    ]);' . PHP_EOL;
        $__appRepositories .= '};' . PHP_EOL;

        $__appRepositories .= PHP_EOL;

        $__appRepositories = "<?php " . $__appRepositories . "?>";

        /**
         * All routes
         */

        $__appRoutes = PHP_EOL;
        $__appRoutes .= PHP_EOL;

        $__appRoutes .= 'use Slim\App;' . PHP_EOL;
        $__appRoutes .= 'use Slim\Interfaces\RouteCollectorProxyInterface as Group;' . PHP_EOL;
        $__appRoutes .= 'use App\DefaultAction;' . PHP_EOL;
        $__appRoutes .= PHP_EOL;
        $__appRoutes .= 'return function (App $app) {' . PHP_EOL;
        $__appRoutes .= "    \$app->get('/', DefaultAction::class);" . PHP_EOL;
        $__appRoutes .= PHP_EOL;
        $__appRoutes .= "    \$app->group('/api', function (Group \$group) {" . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__appRoutes .= "        (require __DIR__ . '/../app/" . ucfirst($index) . "/" . $index . "_route.php')(\$group);" . PHP_EOL;
        }
        $__appRoutes .= '    });' . PHP_EOL;
        $__appRoutes .= PHP_EOL;
        $__appRoutes .= "    \$app->group('/public', function (Group \$group) {" . PHP_EOL;
        $__appRoutes .= '    });' . PHP_EOL;
        $__appRoutes .= '};';

        $__appRoutes .= PHP_EOL;

        $__appRoutes = "<?php " . $__appRoutes . "?>";

        /**
         * Default Action
         */

        $__appDefaultAction = PHP_EOL;
        $__appDefaultAction .= PHP_EOL;

        $__appDefaultAction .= 'namespace App;' . PHP_EOL;
        $__appDefaultAction .= PHP_EOL;
        $__appDefaultAction .= 'use Psr\Http\Message\ResponseInterface as Response;' . PHP_EOL;
        $__appDefaultAction .= 'use Sophy\Application\Actions\Action;' . PHP_EOL;
        $__appDefaultAction .= 'use Sophy\Settings\SettingsInterface;' . PHP_EOL;
        $__appDefaultAction .= PHP_EOL;
        $__appDefaultAction .= 'class DefaultAction extends Action' . PHP_EOL;
        $__appDefaultAction .= '{' . PHP_EOL;
        $__appDefaultAction .= "    const API_VERSION = '1.0.0';" . PHP_EOL;
        $__appDefaultAction .= PHP_EOL;
        $__appDefaultAction .= '    protected function action(): Response' . PHP_EOL;
        $__appDefaultAction .= '    {' . PHP_EOL;
        $__appDefaultAction .= '        $settings = $this->container->get(SettingsInterface::class);';
        $__appDefaultAction .= "        \$appSettings = \$settings->get('app');" . PHP_EOL;
        $__appDefaultAction .= PHP_EOL;
        $__appDefaultAction .= '        $endpoints = [' . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__appDefaultAction .= "            '" . $index . "' => \$appSettings['domain'] . '/api/" . $index . "'," . PHP_EOL;
        }
        $__appDefaultAction .= '        ];' . PHP_EOL;
        $__appDefaultAction .= '        $data = [' . PHP_EOL;
        $__appDefaultAction .= "            'endpoints' => \$endpoints," . PHP_EOL;
        $__appDefaultAction .= "            'version' => self::API_VERSION," . PHP_EOL;
        $__appDefaultAction .= "            'timestamp' => time()" . PHP_EOL;
        $__appDefaultAction .= '        ];' . PHP_EOL;
        $__appDefaultAction .= "        return \$this->respondWithData(\$data, 'Data Services');" . PHP_EOL;
        $__appDefaultAction .= '    }' . PHP_EOL;
        $__appDefaultAction .= '}' . PHP_EOL;

        $__appDefaultAction .= PHP_EOL;

        $__appDefaultAction = "<?php " . $__appDefaultAction . "?>";

        $this->_writeFile($__appDefaultAction, $this->targetExportApp . "DefaultAction.php");
        $this->_writeFile($__appRepositories, $this->targetExportApp . "repositories.php");
        $this->_writeFile($__appRoutes, $this->targetExportApp . "routes.php");
    }

    function generateControllerFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseActions';

        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportApp . ucfirst($index) . '/Application/Actions';
            $this->rcopy($source, $target);

            $this->replaceFileContent($target . '/Base.php', $index);
            $this->replaceFileContent($target . '/Create.php', $index);
            $this->replaceFileContent($target . '/CreateValidator.php', $index);
            $this->replaceFileContent($target . '/GetAll.php', $index);
            $this->replaceFileContent($target . '/GetByBody.php', $index);
            $this->replaceFileContent($target . '/GetByQuery.php', $index);
            $this->replaceFileContent($target . '/GetOne.php', $index);
            $this->replaceFileContent($target . '/Update.php', $index);
        }

    }

    function generateEntityFilesByTable()
    {

        foreach ($this->allTables as $indexTable => $table) {
            $__srcEntity = PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "namespace App\\" . ucfirst($indexTable) . "\Domain\Entities;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "use Sophy\Domain\BaseEntity;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "final class " . ucfirst($indexTable) . " extends BaseEntity" . PHP_EOL;
            $__srcEntity .= "{" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "    protected \$fillable = [" . PHP_EOL;
            foreach ($table as $indexField => $field) {
                $__srcEntity .= "        '" . $table[$indexField]->key . "'," . PHP_EOL;
            }
            $__srcEntity .= "    ];" . PHP_EOL;

            $__srcEntity .= PHP_EOL;

            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $__srcEntity .= "    public function set" . ucwords($field) . "($" . $field . "){ " . PHP_EOL;
                $__srcEntity .= "        \$this->setAttribute('" . $field . "', \$" . $field . ");" . PHP_EOL;
                $__srcEntity .= "    }" . PHP_EOL;
                $__srcEntity .= PHP_EOL;

                $__srcEntity .= "    public function get" . ucwords($field) . "(){ " . PHP_EOL;
                $__srcEntity .= "        return \$this->getAttribute('" . $field . "');" . PHP_EOL;
                $__srcEntity .= "    }" . PHP_EOL;
                $__srcEntity .= PHP_EOL;

            }
            $__srcEntity .= "}" . PHP_EOL;

            $__srcEntity = "<?php " . $__srcEntity . "?>";

            $dir = $this->targetExportApp . ucfirst($indexTable) . '/Domain/Entities';

            @mkdir($dir, 0777, true);

            $this->_writeFile($__srcEntity, $dir . '/' . ucfirst($indexTable) . ".php");
        }
    }

    function generateDTOFilesByTable()
    {

        foreach ($this->allTables as $indexTable => $table) {
            $__srcEntity = PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "namespace App\\" . ucfirst($indexTable) . "\Application\DTO;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "final class " . ucfirst($indexTable) . "DTO" . PHP_EOL;
            $__srcEntity .= "{" . PHP_EOL;
            foreach ($table as $indexField => $field) {
                $__srcEntity .= "    public $" . $table[$indexField]->key . ";" . PHP_EOL;
            }

            $__srcEntity .= "}" . PHP_EOL;

            $__srcEntity = "<?php " . $__srcEntity . "?>";

            $dir = $this->targetExportApp . ucfirst($indexTable) . '/Application/DTO';

            @mkdir($dir, 0777, true);

            $this->_writeFile($__srcEntity, $dir . '/' . ucfirst($indexTable) . "DTO.php");
        }
    }

    function generateExceptionFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseException.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportApp . ucfirst($index) . '/Domain/Exceptions/' . ucfirst($index) . 'Exception.php';
            @mkdir($this->targetExportApp . ucfirst($index) . '/Domain/Exceptions');
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateRepositoryFilesByTable()
    {
        $iSource = $this->sourceFactory . 'TemplateBase/IObjectbaseRepository.php';
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseRepository.php';
        foreach ($this->allTables as $index => $table) {
            @mkdir($this->targetExportApp . ucfirst($index) . '/Infrastructure');
            $iTarget = $this->targetExportApp . ucfirst($index) . '/Domain/I' . ucfirst($index) . 'Repository.php';
            $target = $this->targetExportApp . ucfirst($index) . '/Infrastructure/' . ucfirst($index) . 'RepositoryMysql.php';
            copy($iSource, $iTarget);
            copy($source, $target);
            $this->replaceFileContent($iTarget, $index);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateRouteFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseRoute.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportApp . ucfirst($index) . '/' . $index . '_route.php';
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateServiceContainerFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseServices';

        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportApp . ucfirst($index) . '/Application/Services';
            $this->rcopy($source, $target);

            $this->replaceFileContent($target . '/Base.php', $index);
            $this->replaceFileContent($target . '/CreateService.php', $index);
            $this->replaceFileContent($target . '/FindService.php', $index);
            $this->replaceFileContent($target . '/UpdateService.php', $index);
        }
    }

    function generateServiceFilesByTable()
    {
        foreach ($this->allTables as $indexTable => $table) {

            $tableWithEmail = false;
            $tableWithDescription = false;
            $tableWithName = false;
            $tableWithStatus = false;

            // perform fields and accessor generation
            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                if (strpos($field, 'correo')) {
                    $tableWithEmail = true;
                    break;
                }
                if (strpos($field, 'descripcion')) {
                    $tableWithDescription = true;
                    break;
                }
                if (strpos($field, 'nombre')) {
                    $tableWithName = true;
                    break;
                }
                if (strpos($field, 'estado')) {
                    $tableWithStatus = true;
                    break;
                }
            }

            @mkdir($this->targetExportSrc . "Service/" . ucwords($indexTable));

            /**
             * Base file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucfirst($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Repository\\" . ucfirst($indexTable) . "Repository;" . PHP_EOL;
            $__srcService .= "use App\Service\BaseService;" . PHP_EOL;
            $__srcService .= "use App\Exception\\" . ucfirst($indexTable) . " as " . ucfirst($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= "use Respect\Validation\Validator as validator;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "abstract class Base extends BaseService" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public \$" . $indexTable . "Repository;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function __construct(" . ucfirst($indexTable) . "Repository \$" . $indexTable . "Repository)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$this->" . $indexTable . "Repository = \$" . $indexTable . "Repository;" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            if ($tableWithName) {
                $__srcService .= "    protected static function validar" . ucfirst($indexTable) . "Nombre(\$name)" . PHP_EOL;
                $__srcService .= "    {" . PHP_EOL;
                $__srcService .= "        if (!validator::length(1, 50)->validate(\$name)) {" . PHP_EOL;
                $__srcService .= "            throw new " . ucfirst($indexTable) . "Exception('El nombre es inválido.', 400);" . PHP_EOL;
                $__srcService .= "        }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "        return \$name;" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithDescription) {
                $__srcService .= "    protected static function validar" . ucfirst($indexTable) . "Descripcion(\$description)" . PHP_EOL;
                $__srcService .= "    {" . PHP_EOL;
                $__srcService .= "        if (!validator::length(1, 50)->validate(\$description)) {" . PHP_EOL;
                $__srcService .= "            throw new " . ucfirst($indexTable) . "Exception('La descripcion es inválida.', 400);" . PHP_EOL;
                $__srcService .= "        }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "        return \$description;" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithEmail) {
                $__srcService .= "protected static function validar" . ucfirst($indexTable) . "Correo(\$emailValue)" . PHP_EOL;
                $__srcService .= "{" . PHP_EOL;
                $__srcService .= "    \$email = filter_var(\$emailValue, FILTER_SANITIZE_EMAIL);" . PHP_EOL;
                $__srcService .= "    if (!validator::email()->validate(\$email)) {" . PHP_EOL;
                $__srcService .= "        throw new " . ucfirst($indexTable) . "Exception('Correo invalido', 400);" . PHP_EOL;
                $__srcService .= "     }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "     return (string)\$email;" . PHP_EOL;
                $__srcService .= "}" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithStatus) {
                $__srcService .= "protected static function validate" . ucfirst($indexTable) . "Estado(\$status)" . PHP_EOL;
                $__srcService .= "{" . PHP_EOL;
                $__srcService .= "    if (!validator::numeric()->between(0, 1)->validate(\$status)) {" . PHP_EOL;
                $__srcService .= "       throw new " . ucfirst($indexTable) . "Exception('Estado invalido', 400);" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "    return \$status;" . PHP_EOL;
                $__srcService .= "}" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            $__srcService .= "    protected function get" . ucfirst($indexTable) . "FromDb(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->checkAndGet" . ucfirst($indexTable) . "OrFail(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Base.php");

            /**
             * Create file
             */

            $fieldsToValidate = array();

            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $data = $table[$indexField]->data;
                if ($data['Null'] == 'NO' && $data['Key'] != 'PRI') {
                    $fieldsToValidate[] .= $field;
                }
            }

            $fieldsToValidate = count($fieldsToValidate) ? "'" . implode("', '", $fieldsToValidate) . "'" : '';

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Exception\\" . ucwords($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= "use App\Utils\FieldValidator;" . PHP_EOL;
            $__srcService .= "use App\Entity\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= "use App\Utils;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Create extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    use FieldValidator;" . PHP_EOL;
            $__srcService .= "    private \$fieldsRequired = array(" . $fieldsToValidate . ");" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function create(\$input)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$data = \$this->validate" . ucwords($indexTable) . "Data(\$input);" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->create(\$data);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    private function validate" . ucwords($indexTable) . "Data(\$input)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$fieldsException = \$this->validator(\$input);" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        if (count(\$fieldsException)) {" . PHP_EOL;
            $__srcService .= "          throw new " . ucwords($indexTable) . "Exception('El/los campos ' . GenericUtils::arrayValuesToString(\$fieldsException, ', ') . ' son requerido(s).', 400);" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        return new " . ucwords($indexTable) . "(\$input);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Create.php");

            /**
             * Delete file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Delete extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function delete(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$" . $indexTable . " = \$this->get" . ucwords($indexTable) . "FromDb(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "        \$this->" . $indexTable . "Repository->delete(\$" . $indexTable . ");" . PHP_EOL;
            $__srcService .= "        return \$" . $indexTable . ";" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Delete.php");

            /**
             * Find file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Find extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function get" . ucwords($indexTable) . "sByPage(\$page, \$perPage)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        if (\$page < 1) {" . PHP_EOL;
            $__srcService .= "            \$page = 1;" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= "        if (\$perPage < 1) {" . PHP_EOL;
            $__srcService .= "            \$perPage = self::DEFAULT_PER_PAGE_PAGINATION;" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= "        \$criteria = array('page' => \$page, 'perPage' => \$perPage);" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->fetchRowsByCriteria(\$criteria);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function getAll()" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->fetchRowsByCriteria();" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function get" . ucwords($indexTable) . "(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->get" . ucwords($indexTable) . "FromDb(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Find.php");

            /**
             * Update file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Utils\FieldValidator;" . PHP_EOL;
            $__srcService .= "use App\Entity\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= "use App\Exception\\" . ucwords($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= "use App\Utils;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Update extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    use FieldValidator;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    private \$fieldsRequired = array(" . $fieldsToValidate . ");" . PHP_EOL;
            $__srcService .= "    public function update(\$input, \$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$data = \$this->validate" . ucwords($indexTable) . "Data(\$input, \$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->update(\$data);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    private function validate" . ucwords($indexTable) . "Data(\$input, \$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$fieldsException = \$this->validator(\$input);" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        if (count(\$fieldsException)) {" . PHP_EOL;
            $__srcService .= "          throw new " . ucwords($indexTable) . "Exception('El/los campos ' . GenericUtils::arrayValuesToString(\$fieldsException, ', ') . ' son requerido(s).', 400);" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        \$" . $indexTable . "ToUpdate = \$this->get" . ucwords($indexTable) . "FromDb(\$" . ucwords($indexTable) . "Id);" . PHP_EOL;
            $__srcService .= "        if (!isset(\$" . $indexTable . "ToUpdate)) {" . PHP_EOL;
            $__srcService .= "          throw new " . ucwords($indexTable) . "Exception('No se encontro el registro con el identificador ' . \$" . $indexTable . "Id, 400);" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= "        return new " . ucwords($indexTable) . "(\$input);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Update.php");

        }
    }

    private function rcopy($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target, 0777, true);
            $d = dir($source);
            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->rcopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    function _writeFile($fClass, $fName)
    {

        if (!$handle = fopen($fName, 'w')) {

            exit;
        }

        if (fwrite($handle, $fClass) === FALSE) {
            exit;
        }
        fclose($handle);

    }

    private function replaceFileContent($target, $replacement, $valueToChange = 'objectbase')
    {
        $content1 = file_get_contents($target);
        if ($valueToChange == 'objectbase') {
            $content2 = preg_replace("/" . 'Objectbase' . "/", ucfirst($replacement), $content1);
            $content3 = preg_replace("/" . 'objectbase' . "/", $replacement, $content2);
        } else {
            $content3 = preg_replace("/" . $valueToChange . "/", $replacement, $content1);
        }
        file_put_contents($target, $content3);
    }

}
