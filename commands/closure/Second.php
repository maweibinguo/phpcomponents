<?php
	namespace app\commands\closure;

	class Second extends First
	{
		public function hget()
		{
			echo "Second::hget","\r\n";
		}

		protected function hset()
		{
			echo "Second::hset","\r\n";
		}

		private function _hgetall()
		{
			echo "Second::_hgetall","\r\n";
		}
	}
?>