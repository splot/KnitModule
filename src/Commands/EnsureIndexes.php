<?php
namespace Splot\KnitModule\Commands;

use MD\Foundation\Utils\FilesystemUtils;

use Splot\Framework\Console\AbstractCommand;

class EnsureIndexes extends AbstractCommand 
{

    protected static $name = 'indexes:ensure';
    protected static $description = 'Ensures that all defined indexes on all entities are created.';

    protected static $arguments = array();

    public function execute() {
        $knit = $this->get('knit');

        foreach($this->get('knit.entity_finder')->all() as $name) {
            $this->writeln('Checking <info>'. $name .'</info>');

            $repository = $knit->getRepository($name);
            
            $indexes = $repository->ensureIndexes();

            foreach($indexes as $name => $index) {
                $indexName = is_string($name) ? $name : implode(', ', array_keys($index['properties']));
                if ($index['status']) {
                    $this->writeln('    Ensured index <comment>'. $indexName .'</comment>');
                } else {
                    $this->writeln('    <error>Could not create index on '. $indexName .'</comment>');
                }
            }

            $this->writeln();
        }
    }

}