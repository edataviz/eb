@foreach( $groups as $group )
		@include($group['name'],['data'=>$group['data']])
@endforeach
