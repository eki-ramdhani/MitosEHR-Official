/*
 * JasperReports - Free Java Reporting Library.
 * Copyright (C) 2001 - 2011 Jaspersoft Corporation. All rights reserved.
 * http://www.jaspersoft.com
 *
 * Unless you have purchased a commercial license agreement from Jaspersoft,
 * the following license terms apply:
 *
 * This program is part of JasperReports.
 *
 * JasperReports is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JasperReports is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JasperReports. If not, see <http://www.gnu.org/licenses/>.
 */
package net.sf.jasperreports.web.servlets;

import javax.servlet.http.HttpServletRequest;

import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.util.JRProperties;
import net.sf.jasperreports.web.WebReportContext;
import net.sf.jasperreports.web.util.VelocityUtil;

import org.apache.velocity.VelocityContext;


/**
 * @author Teodor Danciu (teodord@users.sourceforge.net)
 * @version $Id: DefaultViewer.java 4863 2011-12-20 16:07:43Z narcism $
 */
public class DefaultViewer extends AbstractViewer
{
	/**
	 *
	 */
	private static final String RESOURCE_GLOBAL_JS = "net/sf/jasperreports/web/servlets/resources/global.js";
	private static final String RESOURCE_GLOBAL_CSS = "net/sf/jasperreports/web/servlets/resources/global.css";

	private static final String APPLICATION_CONTEXT_PATH_VAR = "APPLICATION_CONTEXT_PATH";

	private static final String PARAMETER_TOOLBAR = "toolbar";
	private static final String PARAMETER_IS_AJAX= "isajax";
	
	private static final String TEMPLATE_HEADER= "net/sf/jasperreports/web/servlets/resources/templates/HeaderTemplate.vm";
	private static final String TEMPLATE_BETWEEN_PAGES= "net/sf/jasperreports/web/servlets/resources/templates/BetweenPagesTemplate.vm";
	private static final String TEMPLATE_FOOTER= "net/sf/jasperreports/web/servlets/resources/templates/FooterTemplate.vm";
	private static final String TEMPLATE_EXCEPTION= "net/sf/jasperreports/web/servlets/resources/templates/ExceptionTemplate.vm";

	protected String getHeader(HttpServletRequest request, WebReportContext webReportContext, boolean hasPages)
	{
		VelocityContext headerContext = new VelocityContext();
		if (hasPages) 
		{
			String webResourcesBasePath = JRProperties.getProperty("net.sf.jasperreports.web.resources.base.path");//FIXMEJIVE reuse this code
			if (webResourcesBasePath == null)
			{
				webResourcesBasePath = request.getContextPath() + ResourceServlet.DEFAULT_PATH + "?" + ResourceServlet.RESOURCE_URI + "=";
			}
			headerContext.put("isAjax", request.getParameter(PARAMETER_IS_AJAX) != null && request.getParameter(PARAMETER_IS_AJAX).equals("true"));
			headerContext.put("contextPath", request.getContextPath());
			headerContext.put("globaljs", webResourcesBasePath + RESOURCE_GLOBAL_JS);
			headerContext.put("globalcss", webResourcesBasePath + RESOURCE_GLOBAL_CSS);
	//		headerContext.put("showToolbar", request.getParameter(PARAMETER_TOOLBAR) != null && request.getParameter(PARAMETER_TOOLBAR).equals("true"));
			headerContext.put("showToolbar", Boolean.TRUE);
			headerContext.put("toolbarId", "toolbar_" + request.getSession().getId() + "_" + (int)(Math.random() * 99999));
			headerContext.put("currentUrl", getCurrentUrl(request, webReportContext));
			headerContext.put("strRunReportParam", ReportServlet.REQUEST_PARAMETER_RUN_REPORT + "=false");
	
			JasperPrint jasperPrint = (JasperPrint)webReportContext.getParameterValue(WebReportContext.REPORT_CONTEXT_PARAMETER_JASPER_PRINT);
			headerContext.put("totalPages", jasperPrint.getPages().size());
	
			String reportPage = request.getParameter(REQUEST_PARAMETER_PAGE);
			headerContext.put("currentPage", (reportPage != null ? reportPage : "0"));
			return VelocityUtil.processTemplate(TEMPLATE_HEADER, headerContext);
		} else 
		{
			return VelocityUtil.processTemplate(AbstractViewer.TEMPLATE_HEADER_NOPAGES, headerContext);
		}
		
	}

	protected String getBetweenPages(HttpServletRequest request, WebReportContext webReportContext) 
	{
		VelocityContext betweenPagesContext = new VelocityContext();
		return VelocityUtil.processTemplate(TEMPLATE_BETWEEN_PAGES, betweenPagesContext);
	}

	protected String getFooter(HttpServletRequest request, WebReportContext webReportContext, boolean hasPages) 
	{
		VelocityContext footerContext = new VelocityContext();
		if (hasPages) {
			return VelocityUtil.processTemplate(TEMPLATE_FOOTER, footerContext);
		} else 
		{
			return VelocityUtil.processTemplate(AbstractViewer.TEMPLATE_FOOTER_NOPAGES, footerContext);
		}
	}

}
