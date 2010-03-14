<!-- BEGIN: POLL_VIEW -->
{POLL_FORM_BEGIN}
<table class="flat">
<!-- BEGIN: POLLTABLE -->
	<tr>
		<td><label>{POLL_INPUT}{POLL_OPTIONS}</label></td>
	</tr>
<!-- END: POLLTABLE -->
	<tr>
		<td>{POLL_FORM_BUTTON}</td>
	</tr>
</table>
{POLL_FORM_END}
<!-- END: POLL_VIEW -->

<!-- BEGIN: POLL_VIEW_VOTED -->
<table class="main">
<!-- BEGIN: POLLTABLE -->
	<tr class="small">
		<td>{POLL_OPTIONS}</td>
		<td class="textright">{POLL_PER}% ({POLL_COUNT})</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="bar_back">
				<div class="bar_front" style="width:{POLL_PER}%;"></div>
			</div>
		</td>
	</tr>
<!-- END: POLLTABLE -->
</table>
<script type="text/javascript">
	function anim(){
		$(".bar_front").each(function(){
			var percentage = $(this).width();
			if (percentage!=""){$(this).width(0).animate({width: percentage}, "slow");}
		});
	}
	anim();
</script>
<p>{PHP.skinlang.ratings.Votes}: {POLL_VOTERS}</p>
<!-- END: POLL_VIEW_VOTED -->


<!-- BEGIN: POLL_VIEW_DISABLED -->
<table>
	<!-- BEGIN: POLLTABLE -->
	<tr>
		<td>{POLL_OPTIONS}</td>
	</tr>
	<!-- END: POLLTABLE -->
	<tr>
		<td>{PHP.L.rat_registeredonly}</td>
	</tr>
</table>
<!-- END: POLL_VIEW_DISABLED -->

<!-- BEGIN: POLL_VIEW_LOCKED -->
<table>
<!-- BEGIN: POLLTABLE -->
	<tr>
		<td>{POLL_OPTIONS}</td>
		<td align="right">{POLL_PER}% ({POLL_COUNT})</td>
	</tr>
	<tr>
		<td colspan="2" align="right">
			<div>
				<div class="bar_back">
					<div class="bar_front" style="width:{POLL_PER}%;"></div>
				</div>
			</div>
		</td>
	</tr>
<!-- END: POLLTABLE -->
</table>
<p>{PHP.skinlang.page.Date} {POLL_SINCE_SHORT} {PHP.skinlang.ratings.Votes} {POLL_VOTERS} </p>
<!-- END: POLL_VIEW_LOCKED -->

<!-- BEGIN: INDEXPOLLS -->

<!-- BEGIN: POLL -->
<a class="strong" href="{IPOLLS_URL}">{IPOLLS_TITLE}</a>
{IPOLLS_FORM}
<!-- END: POLL -->

<!-- BEGIN: ERROR -->
<div class="error">{IPOLLS_ERROR}</div>
<!-- END: ERROR -->

<p class="small textcenter">{IPOLLS_ALL}</p>
<!-- END: INDEXPOLLS -->