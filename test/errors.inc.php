<?php

try {
	$db->string()->rowId('table', 'error');
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: string');
}




try {
	$db->string()->rowId('table', false);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: boolean');
}




try {
	$db->string()->rowId('table', 3);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: integer');
}




try {
	$db->string()->rowId('table', true);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: boolean');
}




try {
	$db->string()->rowId('table', 5.5);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: double');
}




try {
	$db->string()->rowId('table', INF);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: double');
}




try {
	$db->string()->rowId('table', -INF);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: double');
}




try {
	$db->string()->rowId('table', NAN);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for $column: double');
}




try {
	$db->string()->rowId('table', 'column', new stdClass);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Undefined property: stdClass::column');
}




try {
	$db->string()->rowId('table', new stdClass);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Undefined method: stdClass::pudlId');
}




try {
	class test_pudlId_1 implements pudlId {
		public function pudlId() { return false; }
	}
	$db->string()->rowId('table', new test_pudlId_1);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Object retuned invalid value from pudlId');
}




try {
	$db->string()->rowId('table', 'error', fopen(__FILE__, 'r'));
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type: resource');
}




try {
	$db->string()->update('table', '', 'column=1');
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Update data cannot be empty');
}




try {
	$db->string()->update('table', [], 'column=1');
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Update data cannot be empty');
}





try {
	$db->string()->row('table', ['x=']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: x=');
}





try {
	$db->string()->row('table', ['=x']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: =x');
}





try {
	$db->string()->row('table', ['x=y=']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: x=y=');
}





try {
	$db->string()->row('table', ['x=y=z']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: x=y=z');
}





try {
	$db->string()->row('table', ['x<<1']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: x<<1');
}





try {
	$db->string()->row('table', ['x><1']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid clause: x><1');
}





try {
	$db->string()->row('table', [[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[['a']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Recursion limit reached for value expression');
}
