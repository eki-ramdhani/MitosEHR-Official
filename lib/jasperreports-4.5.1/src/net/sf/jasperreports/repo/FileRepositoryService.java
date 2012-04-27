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
package net.sf.jasperreports.repo;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

import net.sf.jasperreports.engine.JRRuntimeException;



/**
 * @author Teodor Danciu (teodord@users.sourceforge.net)
 * @version $Id: FileRepositoryService.java 4962 2012-02-01 15:02:43Z teodord $
 */
public class FileRepositoryService implements StreamRepositoryService
{
	private String root;
	private boolean resolveAbsolutePath;//FIXMEREPO consider giving up on this
	
	/**
	 * 
	 */
	public FileRepositoryService(String root, boolean resolveAbsolutePath)
	{
		this.root = root;
		this.resolveAbsolutePath = resolveAbsolutePath;
	}
	
	/**
	 * 
	 */
	public void setRoot(String root)
	{
		this.root = root;
	}
	
	/**
	 * 
	 */
	public String getRoot()
	{
		return root;
	}
	
	/**
	 * 
	 */
	public void setContext(RepositoryContext context) //FIXMEREPO the context is useless here; consider refactoring
	{
	}
	
	public void revertContext()
	{
	}

	/**
	 * 
	 */
	public InputStream getInputStream(String uri)
	{
		File file = null;

		if (uri != null)
		{
			file = new File(getRoot(), uri);
			if (!file.exists() || !file.isFile())
			{
				if (resolveAbsolutePath)
				{
					file = new File(uri);
					if (!file.exists() || !file.isFile())
					{
						file = null;
					}
				}
				else
				{
					file = null;
				}
			}

		}

		InputStream is = null;

		if (file != null)
		{
			try
			{
				is = new FileInputStream(file);
			}
			catch (IOException e)
			{
				throw new JRRuntimeException(e);
			}
		}
		
		return is;
	}
	
	/**
	 * 
	 */
	public OutputStream getOutputStream(String uri)
	{
		File file = null;

		if (uri != null)
		{
			file = new File(getRoot(), uri);
		}

		OutputStream os = null;

		if (file != null)
		{
			try
			{
				os = new FileOutputStream(file);
			}
			catch (IOException e)
			{
				throw new JRRuntimeException(e);
			}
		}
		
		return os;
	}
	
	/**
	 * 
	 */
	public Resource getResource(String uri)
	{
		throw new JRRuntimeException("Not implemented.");//FIXMEREPO
	}
	
	/**
	 * 
	 */
	public void saveResource(String uri, Resource resource)
	{
		PersistenceService persistenceService = PersistenceUtil.getPersistenceService(FileRepositoryService.class, resource.getClass());
		if (persistenceService != null)
		{
			persistenceService.save(resource, uri, this);
		}
	}
	
	/**
	 * 
	 */
	public <K extends Resource> K getResource(String uri, Class<K> resourceType)
	{
		PersistenceService persistenceService = PersistenceUtil.getPersistenceService(FileRepositoryService.class, resourceType);
		if (persistenceService != null)
		{
			return (K)persistenceService.load(uri, this);
		}
		return null;
	}
}
