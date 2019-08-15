//###########################################################################// 
// Libs for pagination
// ############################################################################

// ============================================
// get the parent uid of current page
// ============================================
lib.pageUp = RECORDS
lib.pageUp {
	source.data = leveluid:-1
	tables = pages
	conf.pages = TEXT
	conf.pages.field = pid
}

// ============================================
// get the uid of current page's next sibling
// ============================================
lib.pageNext = CONTENT
lib.pageNext {
	table = pages
	select {
		pidInList.cObject < lib.pageUp
		orderBy = sorting ASC
		where = nav_hide = 0 AND hidden= 0 AND doktype IN (0,1)
		where.dataWrap = | AND sorting > {field:sorting}
		max = 1
	}
	renderObj = TEXT
	renderObj.field = uid
}

// ============================================
// get the uid of the parents page's next sibling
// ============================================
lib.pageNextParent < lib.pageNext
lib.pageNextParent  {
    select {
        pidInList.cObject.source.data = leveluid:-2
        where.dataWrap = | AND sorting > (SELECT sorting FROM pages WHERE uid = {field:pid})
    }
    renderObj = TEXT
    renderObj.field = uid
}


// ============================================
// get the uid of the first child of current page
// ============================================
lib.pageNextSub = CONTENT
lib.pageNextSub{
    table = pages
    select {
        pidInList = this
        orderBy = sorting ASC
        where = nav_hide= 0 AND hidden= 0 AND doktype IN (0,1)
        max = 1
    }

    renderObj = TEXT
    renderObj.field = uid
}


// ============================================
// get the id of current page's previous sibling
// ============================================
lib.pagePrev = CONTENT
lib.pagePrev {
	table = pages
	select {
		pidInList.cObject < lib.pageUp
		orderBy = sorting DESC
		where = nav_hide = 0 AND hidden = 0 AND doktype IN (0,1)
		where.dataWrap = | AND sorting < {field:sorting}

		max = 1
	}
	renderObj = TEXT
	renderObj.field = uid
}


// ============================================
// get the id of prev page's last sub page
// ============================================
lib.pagePrevSub = CONTENT
lib.pagePrevSub {
    table = pages
    select {
        pidInList.cObject < lib.pagePrev
        orderBy = sorting DESC
        where = nav_hide = 0 AND hidden = 0 AND doktype IN (0,1)

        max = 1
    }
    renderObj = TEXT
    renderObj.field = uid
}

// ============================================
// get the navigation for next 
//============================================
lib.pageNaviNext = TEXT
lib.pageNaviNext {

	// try to go down to a lower level
	cObject < lib.pageNextSub

	// if not available
	ifEmpty{

		// try the next page on same level
		cObject < lib.pageNext

		// if not available
		ifEmpty{

			// try the next page on higher level
			cObject < lib.pageNextParent
		}
	}
}

// ============================================
// get the navigation for previous
// ============================================
lib.pageNaviPrev = TEXT
lib.pageNaviPrev {

	// try to get the last sub page of the previous page
	cObject < lib.pagePrevSub

	// if not available
	ifEmpty {

		// try the prev page on same level
		cObject < lib.pagePrev

		// if not available
		ifEmpty {

			// try the prev page on a higher level
			cObject < lib.pageUp
		}
	}
}

// ############################################################################
// NAVIGATION- MARKER
// ############################################################################

page {
	10 {
		variables {
		
			readMoreNav = COA
			readMoreNav {

				10 = COA
				10 {

                    10 = TEXT
                    10 {

                        value = Vorherige Seite
                        insertData = 1
                        wrap = <li>|</li>
                        stdWrap {

                            typolink {

                                parameter.cObject < lib.pageNaviPrev
                                ATagParams = class="link link--icon"
                                wrap = <svg class="icon icon--btn-left" width="18" height="18"><use xlink:href="#ui-arrow-left"></use></svg>|
                                ATagBeforeWrap = 1
                            }

                        }

                        // if it is set
                        if.isTrue.typolink {
                            parameter.cObject < lib.pageNaviPrev
                            returnLast = url
                        }

                        // if it not equals the current page!!
                        if.isTrue.if {

                            value.typolink {
                               parameter.cObject < lib.pageNaviPrev
                                returnLast = url
                            }
                            equals.typolink {
                                parameter.data = TSFE:id
                                returnLast = url
                            }
                            negate = 1
                        }
                    }


                    20 = TEXT
                    20 {
                        value = Nächste Seite
                        insertData = 1
                        wrap = <li>|</li>
                        stdWrap {

                            typolink {
                                parameter.cObject < lib.pageNaviNext
                                ATagParams = class="link link--icon"
                                wrap = |<svg class="icon icon--btn-right" width="18" height="18"><use xlink:href="#ui-arrow-right"></use></svg>
                                ATagBeforeWrap = 1

                            }

                        }

                        if.isTrue.typolink {
                            parameter.cObject < lib.pageNaviNext
                            returnLast = url
                        }
                    }

                    wrap = <div class="wrap"><ul class="menu menu--pagination">|</ul></div>
				}

				wrap = <nav>|</nav>

			}


			// =================================================================
			beginReadNav < .readMoreNav
			beginReadNav {

				20 >
				10 {
                    10 >
                    20 {

                        value = Jetzt lesen
                        stdWrap {

                            typolink {
                                ATagParams = class="more-button button icon-circle-right"
                            }

                        }

                        if.isTrue.typolink.parameter.cObject < lib.pageNextSub

                    }

                    wrap >
				}
			}
		}
	}
}