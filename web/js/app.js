var baseurl = 'http://ajax.garapenapp.com/api';
$(document).ready(function(){
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $.ajax({url:baseurl+'/document/active_document/'})
    .done(function(response){
        console.log(response);  
        var data = response;
        var html = '';
        html += [
            '<h1 class="document_title text-center">'+data.title+'</h1>'
        ].join('');

        var articles = data.articles;
        for(i in articles){
            var a = articles[i];
            html +=[
                '<div class="article" data-id="'+a.id+'">',
                '<b class="article-title lead">'+a.title+'</b>',
                '<p class="lead text-muted">',
                    a.description,
                '</p>',
                '</div>'
            ].join('');
        }
        
        $('#main-container').html(html);
    })
});