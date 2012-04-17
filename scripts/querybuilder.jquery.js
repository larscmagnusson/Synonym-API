
	function CheckboxParameter(name, checkbox, onUpdateCallback){
		this.enabled = false;
		this.name = name
		this.$checkbox = checkbox;
		this.onUpdateCallback = onUpdateCallback;
		this.init();
	}
	CheckboxParameter.prototype.init = function(){
		var self = this;	// Aktuell 
		
		self.$checkbox.change(function(){
			self.onChange();	
		});
	}
	CheckboxParameter.prototype.onChange = function(){
		var self = this;	// Aktuell CheckboxParameter instans
		self.enabled = self.$checkbox.is(':checked');	// Om checkboxen är true, är enabled det oxå.
		self.onUpdateCallback();
	}
	CheckboxParameter.prototype.getValue = function()	{
		return this.enabled;
	}
	CheckboxParameter.prototype.toString = function()	{
		return this.name + "=" + this.getValue();
	}

	function DropdownParameter(name, checkbox, dropdown, onUpdateCallback){
		this.enabled = false;
		this.name = name
		this.$checkbox = checkbox;
		this.$dropdown = dropdown;
		this.onUpdateCallback = onUpdateCallback;
		this.init();
	}
	DropdownParameter.prototype.init = function(){
		var self = this;	// Aktuell DropdownParameter 

		self.$checkbox.change(function(){
			self.onChange();	
		});
		
		self.$dropdown.change(function(){
			self.onChange();	
		});
	}
	DropdownParameter.prototype.onChange = function(){
		var self = this;	// Aktuell DropdownParameter instans
		self.enabled = self.$checkbox.is(':checked');	// Om checkboxen är true, är enabled det oxå.
		
		self.toggleDisabled();
		self.onUpdateCallback();
	}
	DropdownParameter.prototype.toggleDisabled = function()	{
		if(this.enabled)	
			this.$dropdown.removeAttr('disabled');
		else
			this.$dropdown.attr('disabled', 'disabled');
		
	}
	DropdownParameter.prototype.getValue = function()	{
		return this.$dropdown.val();
	}
	DropdownParameter.prototype.toString = function()	{
		return this.name + "=" + this.getValue();
	}
	
	function TextParameter(name, textbox, onUpdateCallback){
		this.enabled = false;
		this.name = name
		this.$textbox = textbox;
		this.onUpdateCallback = onUpdateCallback;
		this.init();
	}
	TextParameter.prototype.init = function()	{
		var self = this;	// Aktuell TextParameter 
		self.$textbox.keyup(function(){
			self.onChange();	
		});
	}
	TextParameter.prototype.onChange = function()	{
		var self = this;	// Aktuell TextParameter instans
		
		self.enabled = (self.$textbox.val().length > 0);	// Om det finns text i boxen är den enabled. Annars inte
			
		self.onUpdateCallback();
	}
	TextParameter.prototype.getValue = function()	{
		return this.$textbox.val();
	}
	TextParameter.prototype.toString = function()	{
		return this.name + "=" + this.getValue();
	}
	
	$.fn.QueryBuilder = function(settings){
		var parameters = [];
		var query = {};
		
		var build = function(){
			query.string = '';
			query.object = {};
			
			$(parameters).each(function(){
				if(this.enabled){
					query.string += this.toString() + "&";		// Sparar som sträng
					query.object[this.name] = this.getValue();	// Sparar som objekt
				}
			});
			
			query.string = query.string.substring(0,query.string.length - 1);	// Tar bort sista &
		}
		
		// Funktion som ska anropas när någon parameter ändras.
		var parameterChangeCallback = function(){
			build();
			settings.onchange(query);
		}
		
		// Initierar alla parameterar
		this.each(function(){
			var $self = $(this);
			var parameter;
			
			if($self.hasClass('checkbox')){
				var $checkbox = $(this).find('input[type=checkbox]');
				parameter = new CheckboxParameter($checkbox.attr('name'),$checkbox, parameterChangeCallback);
			
			} else if($self.hasClass('dropdown')){
				var $checkbox = $(this).find('input[type=checkbox]');
				var $select = $self.find('select');
				parameter = new DropdownParameter($checkbox.attr('name'), $checkbox, $select, parameterChangeCallback);
			} else if($self.hasClass('text')) {
				var $textbox = $self.find('input[type=text]');
				parameter = new TextParameter($textbox.attr('name'), $textbox, parameterChangeCallback);
			}
			
			parameters.push(parameter);
		});
	}