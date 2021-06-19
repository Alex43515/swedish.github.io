(function ($) {
    jQuery(window).on('elementor/frontend/init', function () {
        class E_Addons_Form_Query extends elementorModules.frontend.handlers.Base {
            getDefaultSettings() {
                return {
                    selectors: {
                        form: '.elementor-form',
                        fieldsWrapper: '.elementor-form-fields-wrapper',
                        fieldGroup: '.elementor-field-group',
                        stepWrapper: '.elementor-field-type-step',
                        stepField: '.e-field-step',
                        submitWrapper: '.elementor-field-type-submit',
                        submitButton: '[type="submit"]',
                        buttons: '.e-form__buttons',
                        buttonWrapper: '.e-form__buttons__wrapper',
                        button: '.e-form__buttons__wrapper__button',
                        paginationLink: '.elementor-pagination a',
                    },
                };
            }
            getDefaultElements() {
                const selectors = this.getSettings('selectors');
                let elements = {};
                elements.$form = this.$element.find(selectors.form);
                elements.$buttons = this.$element.find(selectors.buttons);
                elements.$fieldsWrapper = elements.$form.children(selectors.fieldsWrapper);
                elements.$stepWrapper = elements.$fieldsWrapper.children(selectors.stepWrapper);
                elements.$stepField = elements.$stepWrapper.children(selectors.stepField);
                elements.$fieldGroup = elements.$fieldsWrapper.children(selectors.fieldGroup);
                elements.$submitWrapper = elements.$fieldsWrapper.children(selectors.submitWrapper);
                elements.$submitButton = elements.$submitWrapper.children(selectors.submitButton);
                elements.$paginationLink = this.$element.find(selectors.paginationLink);
                this.elements = elements;
                return elements;
            }
            handleSubmit(event) {
                event.preventDefault();
                //console.log('submit');
                this.handleDisplayQuery(event, true);
                //return false;
            }
            getFormData() {
                var formData = new FormData(this.elements.$form[0]);
                //formData.append('action', this.getSettings('action'));
                //formData.append('referrer', location.toString());
                formData = this.elements.$form.serializeArray();
                if (this.settings && this.settings.widgets) {
                    jQuery.each(this.settings.widgets, function (index, value) {
                        let form = jQuery('.elementor-element-' + value + ' form');
                        let formTmp = form.serializeArray();
                        //formData.concat(formTmp); 
                        //Array.prototype.push.apply(formData,formTmp); 
                        formTmp.forEach(sourceElement => {
                            let targetElement = formData.find(targetElement => {
                                return sourceElement['name'] === targetElement['name'];
                            });
                            if (!targetElement) {
                                formData.push(sourceElement);
                            }
                        });
                    });
                }
                //console.log(formData);
                return formData;
            }
            handleDisplayQuery(event, first) {
                var self = this,
                    $form = this.elements.$form,
                    $scope = this.$element;
                    
                           
                if (first) {
                    //console.log('.elementor-element-' + this.settings.archive);
                    jQuery('.elementor-element-' + this.settings.archive).animate({
                            opacity: '0.45'
                        }, 500).addClass('elementor-form-waiting');
                    if (this.settings && this.settings.widgets) {
                        jQuery.each(this.settings.widgets, function (index, value) {
                            jQuery('.elementor-element-' + value).animate({
                                opacity: '0.45'
                            }, 500).addClass('elementor-form-waiting');
                        });
                    }
                }
                
                let current_url = window.location.href;
                let base_url = current_url.split('?')[0];
                let params = '';
                let data = this.getFormData();
                //console.log(data);
                jQuery.each(data, function (index, value) {
                    if (value.value) {
                        let name = value.name.replace('form_fields[', '').replace(']', '');
                        if (name != 'referer_title' && name != 'wp_query') {
                            params += (params) ? '&' : '?';                        
                            params += name + '=' + encodeURI(value.value);
                        }
                    }
                });
                let redirect_url = base_url + params;
                //console.log(redirect_url);

                //console.log(this.settings);
                if (this.settings && this.settings.e_form_query_ajax) {
                    //console.log(event);
                    var query = setTimeout(function () {
                        let message = $form.find('.elementor-message');
                        if (message.length) {
                            let elements = message.children('.elementor-element');
                            //console.log(elements);
                            if (elements.length) {
                                window.history.pushState("", "", redirect_url);                                 
                                elements.each(function (index, element) {
                                    let widget_type = jQuery(element).data('widget_type');
                                    //console.log(widget_type);
                                    let selector =  '.elementor-element.elementor-element-' + jQuery(element).data('id');
                                    let original = jQuery(selector).not('.elementor-message .elementor-element');
                                    //console.log(original);
                                    if (original.length) {
                                        original.after(element.outerHTML);
                                        original.remove();
                                    } else {
                                        //console.log($scope);
                                        //console.log(element);
                                        $scope.after(element.outerHTML);
                                        // TODO: import assets
                                    }
                                    let $widget = jQuery(selector);
                                    if (widget_type) {
                                        //console.log('frontend/element_ready/' + widget_type);
                                        elementorFrontend.elementsHandler.runReadyTrigger($widget);
                                        //elementorFrontend.hooks.doAction('frontend/element_ready/' + widget_type, $widget, $);
                                    }
                                    $widget.css('opacity', '0.45');
                                    $widget.animate({
                                        opacity: '1'
                                      }, 100);
                                    self.handlePagination($widget);
                                });
                                message.remove();
                                jQuery('.elementor-form-waiting').animate({ opacity: '1' }, 100).removeClass('elementor-form-waiting');                                
                            } else {
                                //console.log('show message');
                                message.show();
                            }
                        } else {
                            self.handleDisplayQuery(event, false);
                        }
                    }, 100);
                } else {                    
                    window.location.href = redirect_url;
                }
            }
            handlePagination($scope) {
                var self = this;
                $scope.find(this.selectors.paginationLink).on('click', function () {
                    let page = 0;
                    let href = jQuery(this).attr('href'); 
                    //window.history.pushState("", "", href); 

                    let tmp = href.split('?page=');
                    if (tmp.length < 2) {
                        tmp = href.split('&page=');
                    }
                    if (tmp.length < 2) {
                        tmp = href.split('?paged=');
                    }
                    if (tmp.length < 2) {
                        tmp = href.split('&paged=');
                    }
                    if (tmp.length > 1) {
                        page = tmp[1].split('&')[0];
                    }
                    if (!page) {
                        let base = href.split('?')[0];
                        tmp = base.split('/');
                        page = tmp.pop();
                        if (!page) {
                            page = tmp.pop();
                        }
                    }
                    page = Number(page) || 1;
                    //console.log(page);
                    let selector = 'input[name="page"]';
                    if (self.elements.$form.find(selector).length) {
                        self.elements.$form.find(selector).val(page);
                    } else {
                        self.elements.$form.append('<input type="hidden" name="page" value="' + page + '">');
                    }
                    /*selector = 'input[name="paged"]';
                     if (self.elements.$form.find(selector).length) {
                     self.elements.$form.find(selector).val(page);
                     } else {
                     self.elements.$form.append('<input type="hidden" name="paged" value="'+page+'">');
                     }*/
                    self.elements.$form.trigger('submit');
                    return false;
                });
            }
            extraForms() {
                var self = this;
                if (this.settings && this.settings.widgets) {
                    jQuery.each(this.settings.widgets, function (index, value) {
                        let form = jQuery('.elementor-element-' + value + ' form');
                        //console.log(form);
                        form.find('select, input').on('change', function () {
                            //if (self.settings.e_form_query_ajax) {
                                self.setExtraFormData();
                            //}
                            self.elements.$form.trigger('submit');
                        });
                    });
                }
            }
            setExtraFormData() {
                var self = this;
                if (this.settings && this.settings.widgets) {
                    jQuery.each(this.settings.widgets, function (index, value) {
                        let form = jQuery('.elementor-element-' + value + ' form');
                        if (form.length) {
                            let formData = form.serializeArray();
                            //console.log(formData);
                            jQuery.each(formData, function (index, value) {
                                //if (value.value) {
                                let name = value.name;
                                let val = value.value;
                                if (name != 'form_id' && name != 'post_id' && name != 'queried_id') {
                                    let id = 'form-field-' + name.replace('form_fields[', '').replace(']', '');
                                    let selector = '#' + id;
                                    selector = 'input[name="' + name + '"]';
                                    //console.log('set '+id+': '+val);
                                    if (self.elements.$form.find(selector).length) {
                                        if (val) {
                                            self.elements.$form.find(selector).val(val);
                                        } else {
                                            self.elements.$form.find(selector).remove();
                                        }
                                    } else {
                                        self.elements.$form.append('<input type="hidden" id="' + id + '" name="' + name + '" value="' + val + '">');
                                    }
                                }
                                //}                                
                            });
                        }
                    });
                }
            }
            bindEvents() {
                let $scope = jQuery(this.$element);
                this.settings = $scope.data('settings');

                this.selectors = this.getSettings('selectors');
                if (this.elements.$form.data('query')) {
                    this.settings.archive = this.elements.$form.data('archive-id');
                    this.settings.widgets = this.elements.$form.data('widgets');
                    this.settings.e_form_query_ajax = this.elements.$form.data('ajax');                    
                    //console.log(this.settings);
                    if (this.settings.e_form_query_ajax) {
                        let archive = jQuery('.elementor-element.elementor-element-'+this.settings.archive);
                        //console.log(archive);
                        this.handlePagination(archive);
                    }
                    this.extraForms();
                    this.setExtraFormData();
                    this.elements.$form.on('submit', this.handleSubmit.bind(this));
                }
            }
        }
        const E_Addons_Form_Query_Handler = ($element) => {
            // wait until steps are ready
            setTimeout(function () {
                elementorFrontend.elementsHandler.addHandler(E_Addons_Form_Query, {
                    $element,
                });
            }, 100);
        };

        elementorFrontend.hooks.addAction('frontend/element_ready/form.default', E_Addons_Form_Query_Handler);
    });
})(jQuery, window);