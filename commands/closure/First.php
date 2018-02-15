<?php
	namespace app\commands\closure;
	class First
	{
		public function begin()
		{
			echo "First::begin","\r\n";
		}

		protected function rollback()
		{
			echo "First::rollback","\r\n";
		}

		private function commit()
		{
			echo "First::commit","\r\n";
		}
	}
?>