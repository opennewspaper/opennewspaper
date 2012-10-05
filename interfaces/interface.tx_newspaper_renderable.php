<?php
/**
 *  \file interface.tx_newspaper_renderable.php
 *
 *  \author Lene Preuss <lene.preuss@gmail.com>
 *  \date Mar 31, 2009
 */

/// All classes which have a render() function must implement this interface
interface tx_newspaper_Renderable {

	/// Render the Extra using the given Smarty template
	/** \param $template_set Template set used to render the Extra
	 *  \return The rendered HTML
	 */
	public function render($template_set = '');

}

?>