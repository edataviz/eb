const LoginScreen = {

    init: function(){
        $('input[name="password"]').keyup(function(e){ 
            var code = e.which; // recommended to use e.which, it's normalized across browsers
            if(code==13)e.preventDefault();
            if(code==32||code==13||code==188||code==186){
                LoginScreen.login();
            } 
        });
        $('.lnk-forgot-password').click(LoginScreen.forgotPassword);
        $('.box-login-sub-normal .btn-login').click(LoginScreen.login);
        $('.btn-fp-cancel').click(LoginScreen.cancelForgotPassword);
        //$('.btn-fp-submit').click(null);

        const bg = Math.floor(Math.random() * 8) + 1;
        //bg=111;
        $('.box-login-container').css('background-image', 'url(/graphic/bg/'+bg+'.png)');
        $('.box-login-container').show();
        $('input[name="username"]').focus();  
        $('.btn-sso-login').click(function(){
            $('input[name="username"]').val($('.box-login-sso').attr('sso-user'));
            $('input[name="type"]').val('sso');
            $('[form-login]').submit();
        });
        $('.btn-sso-cancel').click(function(){
            $('.box-login-input').removeAttr('sso');
        });
    },

    login: function(){
        if(!$('input[name="username"]').val().trim()){
            alert('Please input username');
            $('input[name="username"]').focus();
            return;
        }
        if(!$('input[name="password"]').val().trim()){
            alert('Please input password');
            $('input[name="password"]').focus();
            return;
        }
        $('[form-login]').submit();
        return;
        $.ajax({
            url: '/auth/eblogin',
            type: "post",
            data: {
                username: $('input[name="username"]').val(),
                password: $('input[name="password"]').val(),
                url: $('.box-login-input').attr('next-url'),
                type: 'basic',
            },
            success: function(data){
                const isLoginSuccess = data.status;
                if(isLoginSuccess){
                    location.reload();
                }
                else{
                    alert('Can not login.\n' + data.message);
                }
            },
            error: function(data){
                console.log(data);
                alert('Error');
            }
        });
    },

    forgotPassword: function(){
        $(".box-login").attr('forgot', '1');
        $("#inp-login-email").focus();   
    },

    cancelForgotPassword: function(){
        $(".box-login").attr('forgot', '');
        $('input[name="username"]').focus();    
    },
}

LoginScreen.init();