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
package net.sf.jasperreports.engine.export.ooxml;

import net.sf.jasperreports.engine.JRGenericPrintElement;
import net.sf.jasperreports.engine.JRPrintElement;
import net.sf.jasperreports.engine.export.ExporterFilter;
import net.sf.jasperreports.engine.export.GenericElementHandler;
import net.sf.jasperreports.engine.export.GenericElementHandlerEnviroment;
import net.sf.jasperreports.engine.export.JExcelApiExporter;
import net.sf.jasperreports.engine.export.JRXlsAbstractExporterNature;

/**
 * @author Teodor Danciu (teodord@users.sourceforge.net)
 * @version $Id: JRXlsxExporterNature.java 4595 2011-09-08 15:55:10Z teodord $
 */
public class JRXlsxExporterNature extends JRXlsAbstractExporterNature
{

	/**
	 *
	 */
	public JRXlsxExporterNature(ExporterFilter filter, boolean isIgnoreGraphics, boolean isIgnorePageMargins)
	{
		super(filter, isIgnoreGraphics, isIgnorePageMargins);
	}

	public boolean isToExport(JRPrintElement element)
	{
		boolean isToExport = true;
		if (element instanceof JRGenericPrintElement)
		{
			JRGenericPrintElement genericElement = (JRGenericPrintElement) element;
			GenericElementHandler handler = GenericElementHandlerEnviroment.getHandler(
					genericElement.getGenericType(), JRXlsxExporter.XLSX_EXPORTER_KEY);
			if (handler == null || !handler.toExport(genericElement))
			{
				isToExport = false;
			}
		}

		return isToExport && super.isToExport(element);
	}
	
}
