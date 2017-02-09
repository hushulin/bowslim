<?php
namespace Bow\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove log dir runtime files
 * name: log:remove
 */
class GenerateModel extends Command
{

	protected function configure()
    {
        $this
            ->setName('jcgen:model')
            ->setDescription('Remove log dir runtime files')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Where do you want to create?'
            )
            // ->addOption(
            //    'yell',
            //    null,
            //    InputOption::VALUE_NONE,
            //    'If set, the task will yell in uppercase letters'
            // )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if (!$name) {
        	$output->writeln('please write model nmae!');
        	return ;
        }

        $_template =
        '<?php
namespace Bow\Model;

use Illuminate\Database\Eloquent\Model;

class {name} extends Model
{
	protected $table = \'{name}\';

	protected $guarded = [\'id\'];

	// protected $fillable = [\'name\'];
}
';

		$code = str_replace('{name}', $name, $_template);

		$dir =  __DIR__ . '/../../bow/database/models/School/'.$name.'.php';

		file_put_contents($dir, $code);

		$output->writeln('success');
    }
}
