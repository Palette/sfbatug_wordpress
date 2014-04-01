/*
 * Script for BuddyPress XProfile Image Field plugin
 * Version:  1.0.0
 * Author : Alex Githatu
 */

(

    function(jQ){
        // exists method (http://stackoverflow.com/questions/31044/is-there-an-exists-function-for-jquery)
        jQuery.fn.exists = function(){
            return this.length > 0;
        };

        //outerHTML method (http://stackoverflow.com/a/5259788/212076)
        jQ.fn.outerHTML = function() {
            $t = jQ(this);
            if( "outerHTML" in $t[0] ){ 
                return $t[0].outerHTML; 
            }
            else
            {
                var content = $t.wrap('<div></div>').parent().html();
                $t.unwrap();
                return content;
            }
        };

        bpxpif =
        {

        init : function(){
                if(jQ("div#poststuff select#fieldtype").exists()){

                    if(!jQ('div#poststuff select#fieldtype option[value="image"]').exists()){
                        var imageOption = '<option value="image">Image</option>';
                        jQ("div#poststuff select#fieldtype").append(imageOption);

                        var selectedOption = jQ("div#poststuff select#fieldtype").find("option:selected");
                        if((selectedOption.length === 0) || (selectedOption.outerHTML().search(/selected/i) < 0)){
                            var action = jQ("div#poststuff").parent().attr("action");

                            if (action.search(/mode=edit_field/i) >= 0){
                                jQ('div#poststuff select#fieldtype option[value="image"]').attr("selected", "selected");
                            }
                        }
                    }

                }
                
                // update the edit form's enctype. this assumes BP Default theme and its child themes
                if(jQ("#profile-edit-form").exists()){
                    
                    jQ("#profile-edit-form").attr("enctype", "multipart/form-data");
                    
                }
            }

        };

        jQ(document).ready(function(){
            bpxpif.init();
        });

    }

)(jQuery);