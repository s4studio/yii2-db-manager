<?php

namespace s4studio\dbManager\models;

use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class PostgresDumpManager.
 */
class PostgresDumpManager extends BaseDumpManager
{
    /**
     * @param $path
     * @param array $dbInfo
     * @param array $dumpOptions
     * @return string
     */
   public function makeDumpCommand($path, array $dbInfo, array $dumpOptions)
    {
        // default port
        if (empty($dbInfo['port'])) {
            $dbInfo['port'] = '5432';
        }
        $arguments = [];
        if (!$this->isWindows()) {
            $arguments[] = "PGPASSWORD='{$dbInfo['password']}'";
        }
        $arguments[] = 'pg_dump';
        if ($dumpOptions['schemaOnly']) {
            $arguments[] = '--schema-only';
        }

        $params = [];
        if ($this->isWindows()) {
            $params = [
                '"',
                'host=' . $dbInfo['host'],
                'port=' . $dbInfo['port'],
                'user=' . $dbInfo['username'],
                'password=' . $dbInfo['password'],
                'dbname=' . $dbInfo['dbName'],
                '"',
            ];
        }
        else {
            $params = [
                '--host=' . $dbInfo['host'],
                '--port=' . $dbInfo['port'],
                '--username=' . $dbInfo['username'],
                '--no-password',
            ];
        }

        $arguments = ArrayHelper::merge($arguments, $params);

        if ($dumpOptions['preset']) {
            $arguments[] = trim($dumpOptions['presetData']);
        }

        if (!$this->isWindows()) {
            $arguments[] = $dbInfo['dbName'];
        }

        if ($dumpOptions['isArchive'] && !$this->isWindows()) {
            $arguments[] = '|';
            $arguments[] = 'gzip';
        }
        $arguments[] = '>';
        $arguments[] = $path;

        return implode(' ', $arguments);
    }

    /**
     * @param $path
     * @param array $dbInfo
     * @param array $restoreOptions
     * @return string
     */
    public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions)
    {
        $arguments = [];
        if (StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = 'gunzip -c';
            $arguments[] = $path;
            $arguments[] = '|';
        }
        if ($this->isWindows()) {
            $arguments[] = "set PGPASSWORD='{$dbInfo['password']}'";
            $arguments[] = '&';
        } else {
            $arguments[] = "PGPASSWORD='{$dbInfo['password']}'";
        }
        // default port
        if (empty($dbInfo['port'])) {
            $dbInfo['port'] = '5432';
        }
        $arguments = ArrayHelper::merge($arguments, [
            'psql',
            '--host=' . $dbInfo['host'],
            '--port=' . $dbInfo['port'],
            '--username=' . $dbInfo['username'],
            '--no-password',
        ]);
        if ($restoreOptions['preset']) {
            $arguments[] = trim($restoreOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if (!StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = '<';
            $arguments[] = $path;
        }

        return implode(' ', $arguments);
    }
}
