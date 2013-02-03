$.merge($.dynaCloud.stopwords, ["direct","link","football","sport","tennis","basket","rugby","news","league","ligue","mais","ou","et","donc","or","ni","car","en","ce","ca","cette","cet","ces","se","ses"]);

  $(document).ready(function() {
    
     // $('.sidebar-nav').affix();
    function scrollToDiv(element,navheight){
      var offset = element.offset();
      var offsetTop = offset.top;
      var totalScroll = offsetTop-navheight;
       
      $('body,html').animate({scrollTop: totalScroll}, 500); 
     }

    function notification(t){

      $('.notification').text(t).fadeIn('fast').delay(2000).fadeOut('fast');

    }

    function showCategory(n){
      $('.box:not([category="'+n+'"])').hide(200);
      $('.box[category="'+n+'"]').show(200);
      $('#cat_zone li').removeClass('active');
      $('#cat_zone li.'+n.toLowerCase()).addClass('active');
    }

    function rssReloader(){

    $('#here').fadeToggle('fast').load(apilink+"?module=getAllRss",function(){
         
           $('.listing .btn').remove();
           $('#cat_zone li').remove();
          
            $('#here .top').each(function(){
         
              $(this).clone().appendTo('.listing').wrap('<div class="btn btn-small" />');
         
            }); 
         
          $('.box').each(function(){
            $cat = $(this).attr('category');
            
            if(!$('#cat_zone li').hasClass($cat.toLowerCase()))
               $('#cat_zone').append('<li class='+$cat.toLowerCase()+'><a href="#">'+$cat+'</a></li>');

          });

          //Selection des bons flux et bouton a gauche selon la catégorie
          showCategory($('#cat_zone li:first-child a').text());


          $('.resume').popover({placement: 'bottom', trigger: 'hover', html: true});
          
          $('#tagcloud').html('');
           $.dynaCloud.max = 5;
          $('#here').dynaCloud('#tagcloud');    

          }).fadeToggle('fast');

          }

    rssReloader();

    setInterval(function(){rssReloader();},180*1000);
    
    $('#goup').click(function(){
  
      scrollToDiv($('.sidebar-nav'))

    });


    $('#cat_zone li a').live("click",function(e){  
      e.preventDefault();   
      showCategory($(this).text());
     });

    $('.seeall').live("click",function(){

      $(this).nextAll('.none').toggleClass('span11');

    });

    $('.listing .top').live("click",function(){

      scrollToDiv($('#here .top[id="'+$(this).attr('id')+'"]'),30);
  
    });

    $('#form-add-rss, #form-add-rss input').keyup(function(e){

      e.preventDefault();

      if (e.keyCode == 13 && !(e.shiftKey || e.ctrlKey || e.altKey)) {

          $('#link_submit').trigger('click');

      }
      
    });

		$('#link_submit').click(function(e){
		e.preventDefault();
			$.getJSON(apilink, {
				module: 'addLink',
				name: $('#link_name').attr('value'),
				link: $('#link').attr('value'),
        category: $('#add_category').attr('value')
			}, function(data) {

        if(data.status == 'OK'){
        
        notification($('#link_name').attr('value')+' ajouté');
         
            $('input[type="text"]').val('');
            rssReloader();
          }

        if(data.status == 'Error')
          notification(data.error);
			});

		});

    $('.refresher_orb').click(function(){rssReloader();});

    $('.link_remover').live("click",function(){
     
    $.getJSON(apilink, {
        module: 'deleteLink',
        id:  $(this).attr('rel')
      }, function(data) {

        if(data.status !== 'KO'){
        
            notification($(this).closest('span').text()+' retiré');

            rssReloader();

          }
      });

    });

   });