@extends('eb.base')

@section('head')
@parent
<link rel="stylesheet" href="/css/home.css">
@stop

@section('main')
<div fav-menu></div>
@stop

@section('script')
@parent
<script>
EB.screenCode = 'ROOT';
delete EB.favState;
//EB.showSideBox();
EB.buildFavMenu();
EB.buildScreen({
	title: 'Home / Shortcuts',
	items: [
        {
            type: 'button',
			content: 'Dashboard',
			css: {'margin-left': '30px'},
            tooltip: 'Open Dashboard',
            click: function(){
				location.href = 'dv-dashboard'
            }
        },
		{
			type: 'html',
			html: `<div box-btn-arrange>
	<button btn-arrange>{{ trans('Config shortcuts') }}</button>
	<button btn-arrange-done>{{ trans('Done') }}</button>
	<span note>{{ trans('Drag item to change position. Select item in the main menu to add.') }}</span>
</div>
`,
			actions: [
                {
                    selector: '[btn-arrange]',
                    event: 'click',
                    action: function(){
						$("[fav-menu]").attr('arrange', '1');
						$("[box-btn-arrange]").attr('arrange', '1');
						$("[fav-menu]").sortable();
						window.isEditingFav = true;
                    }
				},
                {
                    selector: '[btn-arrange-done]',
                    event: 'click',
                    action: function(){
						$("[fav-menu]").removeAttr('arrange');
						$("[box-btn-arrange]").removeAttr('arrange');
						$("[fav-menu]").sortable('destroy');
						window.isEditingFav = false;
						let fav = '';
						$('[fav-menu]').children().each(function(){
							fav += (fav ? ',' : '') + $(this).attr('code');
						});
						fav != EB.workspace.FAV && sendAjax('/save-fav', {fav: fav});
                    }
				},
			]
		},
	],
});
window.isEditingFav = false;
</script>
@stop
