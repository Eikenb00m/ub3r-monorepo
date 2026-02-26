package com.runescape.util;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;

public class Unzip {

	/**
	 * Unzip it
	 * @param zipFile input zip file
	 * @param output zip file output folder
	 * @param deleteAfter		Should the zip file be deleted afterwards?
	 */
	public static boolean unZipIt(String zipFile, String outputFolder, boolean deleteAfter) {

		byte[] buffer = new byte[1024];

		try{

			//create output directory is not exists
			File folder = new File(outputFolder);
			if(!folder.exists()){
				folder.mkdir();
			}

			//get the zip file content
			try (ZipInputStream zis = new ZipInputStream(new FileInputStream(zipFile))) {
				//get the zipped file list entry
				ZipEntry ze = zis.getNextEntry();
				while(ze!=null){

					String fileName = ze.getName();
					File newFile = new File(outputFolder + File.separator + fileName);
					String destinationPath = folder.getCanonicalPath() + File.separator;
					String entryPath = newFile.getCanonicalPath();

					if (!entryPath.startsWith(destinationPath)) {
						throw new IOException("Blocked zip entry outside destination: " + fileName);
					}

					System.out.println("file unzip : "+ newFile.getAbsoluteFile());

					if (ze.isDirectory()) {
						newFile.mkdirs();
						ze = zis.getNextEntry();
						continue;
					}

					//create all non exists folders
					//else you will hit FileNotFoundException for compressed folder
					new File(newFile.getParent()).mkdirs();

					try (FileOutputStream fos = new FileOutputStream(newFile)) {
						int len;
						while ((len = zis.read(buffer)) > 0) {
							fos.write(buffer, 0, len);
						}
					}

					ze = zis.getNextEntry();
				}

				zis.closeEntry();
			}
			
			if(deleteAfter) {
				new File(zipFile).delete();
			}

			return true;

		}catch(Exception ex){
			ex.printStackTrace();
			return false;
		}
	}

}
